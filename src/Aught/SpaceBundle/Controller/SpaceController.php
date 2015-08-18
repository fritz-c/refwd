<?php

namespace Aught\SpaceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Aught\SpaceBundle\Entity\User;
use Aught\SpaceBundle\Entity\Space;
use Aught\SpaceBundle\Entity\Comment;
use Aught\SpaceBundle\Entity\Image;
use Aught\SpaceBundle\Entity\SpaceLink;
use Aught\SpaceBundle\Entity\Email;

use Aught\SpaceBundle\Entity\UserRepository;
use Aught\SpaceBundle\Entity\CommentRepository;
use Aught\SpaceBundle\Entity\RelishRepository;

use Aught\SpaceBundle\Form\Type\ContactType;

class SpaceController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Route("/{_locale}", name="home_lang", requirements={"_locale" = "en|ja"})
     * @Template()
     */
    public function homeAction()
    {
        return array();
    }

    /**
     * @Route("/terms", name="terms")
     * @Route("/{_locale}/terms", name="terms_lang", requirements={"_locale" = "en|ja"})
     * @Template()
     */
    public function termsAction()
    {
        return array();
    }

    /**
     * @Route("/contact", name="contact")
     * @Route("/{_locale}/contact", name="contact_lang", requirements={"_locale" = "en|ja"})
     * @Template()
     */
    public function contactAction(Request $request)
    {
        $form = $this->createForm(new ContactType());

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $message = \Swift_Message::newInstance()
                    ->setSubject($form->get('subject')->getData())
                    ->setFrom($form->get('email')->getData())
                    ->setTo('us.chrisf@gmail.com')
                    ->setBody(
                        $this->renderView(
                            'AughtSpaceBundle:Space:contact_form_message.html.twig',
                            array(
                                'ip' => $request->getClientIp(),
                                'name' => $form->get('name')->getData(),
                                'message' => $form->get('message')->getData()
                            )
                        )
                    );

                $this->get('mailer')->send($message);

                $request->getSession()->getFlashBag()->add('success', 'Your email has been sent! Thanks!');

                return $this->redirect($this->generateUrl('contact'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/view/{token}", name="space_view")
     * @Route("/view/{_locale}/{token}", name="space_view_lang", requirements={"_locale" = "en|ja"})
     * @ParamConverter("space", class="AughtSpaceBundle:Space")
     * @Template()
     */
    public function viewAction(Request $request, Space $space)
    {
        $em = $this->getDoctrine()->getManager();

        $space_rep = $this->getDoctrine()->getRepository('AughtSpaceBundle:Space');
        $user_rep = $this->getDoctrine()->getRepository('AughtSpaceBundle:User');
        $participants = $space_rep->findParticipantsBySpaceId($space->getId());

        $author = null;
        foreach ($participants as $user) {
            // The first() is not a mistake. When performing the select with the repo, only one space link
            // (the one corresponding to this space) is left attached
            if ($user->getSpaceLinks()->first()->getRelation() == SpaceLink::RELATION_FROM) {
                $author = $user;
                break;
            }
        }

        // If an email was included in the params of the url, use it as the preferred commenter
        $viewer = null;
        $pref_users = array();
        if ($viewer_email = $request->query->get('v')) {
            $result = $user_rep->findByEmail($viewer_email);
            if ($result) {
                $viewer = reset($result);
                $pref_users[] = $viewer;
            }
        }

        $comment = new Comment();

        $form = $this->createFormBuilder($comment)
            ->add('author', 'entity', array(
                'class' => 'AughtSpaceBundle:User',
                'choices' => $participants,
                'data' => $viewer,
                'preferred_choices' => $pref_users,
                'property' => 'addressLineFormatMuddle',
                'empty_value' => 'Who are you?',
            ))
            ->add('body', 'textarea')
            ->add('submit', 'submit')
            ->getForm();

        $form->handleRequest($request);

        // ctodo Handle POSTed comments for when websockets don't work. Probably need to solve it on the twig side.
        if ($form->isValid()) {
            $comment->setSpace($space);
            $em->persist($comment);
            $em->flush();
            return $this->redirect($this->generateUrl('aught_space_space_view', array('token' => $space->getToken())));
        }

        // Stick URLs for images we stored in S3 into the HTML
        $body = $space->getBody();
        $images = $space->getImages();
        if ($images) {
            $image_key_map = array();
            $storage = $this->get("storageUtils");
            foreach ($images as $image) {
                $content_id = $image->getContentId();
                $url = $storage->getUrl($image->getAwsKey());

                if (preg_match("/cid:{$content_id}/", $body)) {
                    $body = preg_replace("/[\"\']cid:{$content_id}[^\"\']*[\"\']/", "\"{$url}\"", $body);
                } else {
                    // If it doesn't appear in the message, append it to the end
                    $body .= "<img src=\"{$url}\">";
                }
            }
        }

        // Filter out dangerous elements
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);
        $body = $purifier->purify($body);

        // Obfuscate emails
        $body = preg_replace('/([a-zA-Z0-9._%+-]+@)[a-zA-Z0-9.-]+(\.[a-zA-Z]{2,6})/', '$1****$2', $body);
        $space->setBody($body);

        return $this->render('AughtSpaceBundle:Space:view.html.twig', array(
            'participants' => $participants,
            'author' => $author,
            'space' => $space,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/_mailparser")
     */
    public function mailParseAction(Request $request)
    {
        // Get mail data from POST
        $data = $request->request->get('data');
        $em = $this->getDoctrine()->getManager();

        $email = new Email($data);
        unset($data);

        $em->transactional(function($em) use($email) {
            $email_utils = $this->get('email_utils')->initWithEmail($email);
            $space = $email_utils->registerSpace($em);
            $em->flush(); // Space id from the database is needed for constructing S3 key for images

            $author     = $email_utils->registerAuthor($em, $space);
            $recipients = $email_utils->registerRecipients($em, $space);
            $images     = $email_utils->registerImages($em, $space, $author);

            $email_utils->sendSpaceOpenedEmail($recipients, $space, $author);
        });

        return new JsonResponse(array('result' => 'success'));
    }

    /**
     * @Route("/_socket")
     */
    public function socketAction(Request $request)
    {
        // Get mail data from POST
        $msg = $request->request->get('data');
        if (empty($msg)) throw new \Exception("Invalid JSON for message!", 1);

        $em = $this->getDoctrine()->getManager();
        $space_rep = $this->getDoctrine()->getRepository('AughtSpaceBundle:Space');

        // Find the space corresponding to the token provided by the message
        $space = $space_rep->findByToken($msg['s']);
        if (!$space) throw new \Exception("No such space!", 1);
        $space = reset($space);

        if (!isset($msg['t'])) {
            throw new \Exception("Invalid message from socket: needs type!", 1);
        }

        // $ret = array();
        $em->transactional(function($em) use($msg, $space, $space_rep) {
            $user_rep = $this->getDoctrine()->getRepository('AughtSpaceBundle:User');
            switch ($msg['t']) {
                // Comment on post
                case 'c':
                    $author  = UserRepository::getAuthorFromSocketMessage($msg, $space, $space_rep, $user_rep);
                    $comment = CommentRepository::createCommentFromSocketMessage($msg, $space, $author, $em);

                    // $purifier = new \HTMLPurifier(\HTMLPurifier_Config::createDefault());
                    // $ret['t'] = 'c';
                    // $ret['n'] = $author->getName();
                    // $ret['e'] = $author->getMuddleMail();
                    // $ret['z'] = $comment->getUpdatedAt();
                    // $ret['m'] = $purifier->purify($comment->getBody());
                    break;

                // Post was relished/derelished
                case 'r':
                    $msg['b'] = $msg['b'] === 'true' ? true : false;

                    $author = UserRepository::getAuthorFromSocketMessage($msg, $space, $space_rep, $user_rep);
                    $relish_rep = $this->getDoctrine()->getRepository('AughtSpaceBundle:Relish');
                    RelishRepository::toggleRelish($space, $author, $em, $relish_rep, $msg['b']);

                    // $ret['t'] = 'r';
                    // $ret['n'] = $author->getName();
                    // $ret['e'] = $author->getMuddleMail();
                    break;

                default:
                    break;
            }
        });

        return new JsonResponse(array('result' => 'success'));
    }
}
