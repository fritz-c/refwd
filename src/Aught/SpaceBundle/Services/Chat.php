<?php

namespace Aught\SpaceBundle\Services;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use Aught\SpaceBundle\Entity\UserRepository;
use Aught\SpaceBundle\Entity\CommentRepository;
use Aught\SpaceBundle\Entity\RelishRepository;

class Chat implements MessageComponentInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Aught\SpaceBundle\Entity\UserRepository
     */
    protected $user_rep;

    /**
     * @var \Aught\SpaceBundle\Entity\SpaceRepository
     */
    protected $space_rep;

    /**
     * @var \Aught\SpaceBundle\Entity\RelishRepository
     */
    protected $relish_rep;

    /**
     * Stores chat connections
     *
     * \SplObjectStorage
     */
    protected $clients;

    /**
     * Constructor
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($em, $user_rep, $space_rep, $relish_rep)
    {
        $this->em = $em;
        $this->user_rep = $user_rep;
        $this->space_rep = $space_rep;
        $this->relish_rep = $relish_rep;

        $this->clients = array();

        echo "Listening on port 8443.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        // Get the space token from the uri used in the connection
        $conn->space_token = $conn->WebSocket->request->getQuery()->get('s');

        // Initialize storage for the connection if there is none yet existing for the space
        if (!isset($this->clients[$conn->space_token])) {
            $this->clients[$conn->space_token] = new \SplObjectStorage;
        }

        // Store the new connection to send messages to later
        $this->clients[$conn->space_token]->attach($conn);

        echo "New connection! ({$conn->space_token} ({$conn->resourceId}))\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Ignore pings
        if ($msg === 'p') return;

        echo sprintf('Connection %d sending message "%s"' . "\n", $from->resourceId, $msg);

        // Decode JSON message
        $decoded = json_decode($msg, true);
        if (empty($decoded)) throw new \Exception("Invalid JSON for message!", 1);

        // Find the space corresponding to the token provided in the websocket uri
        $source_space_token = $from->space_token;
        $space = $this->space_rep->findByToken($source_space_token);
        if (!$space) throw new \Exception("No such space!", 1);
        $space = reset($space);

        if (!isset($decoded['t'])) {
            throw new \Exception("Invalid message from socket: needs type!", 1);
        }

        $ret = array();
        switch ($decoded['t']) {
            // Comment on post
            case 'c':
                $author  = UserRepository::getAuthorFromSocketMessage($decoded, $space, $this->space_rep, $this->user_rep);
                $comment = CommentRepository::createCommentFromSocketMessage($decoded, $space, $author, $this->em);

                // $config = \HTMLPurifier_Config::createDefault();
                $purifier = new \HTMLPurifier(\HTMLPurifier_Config::createDefault());
                $ret['t'] = 'c';
                $ret['n'] = $author->getName();
                $ret['e'] = $author->getMuddleMail();
                $ret['z'] = $comment->getUpdatedAt();
                $ret['m'] = $purifier->purify($comment->getBody());
                break;

            // Post was relished/derelished
            case 'r':
                $author = UserRepository::getAuthorFromSocketMessage($decoded, $space, $this->space_rep, $this->user_rep);
                RelishRepository::toggleRelish($space, $author, $this->em, $this->relish_rep);

                $ret['t'] = 'r';
                $ret['n'] = $author->getName();
                $ret['e'] = $author->getMuddleMail();
                break;

            default:
                break;
        }

        foreach ($this->clients[$from->space_token] as $client) {
            // Send to each client in the same space
            $client->send(json_encode($ret));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients[$conn->space_token]->detach($conn);

        // Unset storage for spaces that aren't in use
        if (count($this->clients[$conn->space_token]) < 1) {
            unset($this->clients[$conn->space_token]);
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}