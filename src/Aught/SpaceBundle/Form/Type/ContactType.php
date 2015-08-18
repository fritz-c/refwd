<?php

namespace Aught\SpaceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'contact_form.name',
                'attr' => array(
                    'placeholder' => 'contact_form.placeholder_name',
                    'pattern'     => '.{2,}' //minlength
                )
            ))
            ->add('email', 'email', array(
                'label' => 'contact_form.email',
                'attr' => array(
                    'placeholder' => 'contact_form.placeholder_email'
                )
            ))
            ->add('subject', 'text', array(
                'label' => 'contact_form.subject',
                'attr' => array(
                    'placeholder' => 'contact_form.placeholder_subject',
                    'pattern'     => '.{3,}' //minlength
                )
            ))
            ->add('message', 'textarea', array(
                'label' => 'contact_form.message',
                'attr' => array(
                    'cols' => 90,
                    'rows' => 10,
                    'placeholder' => 'contact_form.placeholder_message'
                )
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $collectionConstraint = new Collection(array(
            'name' => array(
                new NotBlank(array('message' => 'Name should not be blank.')),
                new Length(array('min' => 2))
            ),
            'email' => array(
                new NotBlank(array('message' => 'Email should not be blank.')),
                new Email(array('message' => 'Invalid email address.'))
            ),
            'subject' => array(
                new NotBlank(array('message' => 'Subject should not be blank.')),
                new Length(array('min' => 3))
            ),
            'message' => array(
                new NotBlank(array('message' => 'Message should not be blank.')),
                new Length(array('min' => 5))
            )
        ));

        $resolver->setDefaults(array(
            'constraints' => $collectionConstraint
        ));
    }

    public function getName()
    {
        return 'contact';
    }
}