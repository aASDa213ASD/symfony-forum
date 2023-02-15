<?php

namespace App\Security;

use App\Entity\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PostVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Post && in_array($attribute, ['edit', 'delete']);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var UserInterface|null $user */
        $user = $token->getUser();
        if ($user != null) {
            $username = $user->getUsername();
        }
        else {
            $username = "guest";
        }

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Post $post */
        $post = $subject;

        switch ($attribute) {
            case 'edit':
                return $username === $post->getAuthor() || in_array('ROLE_ADMIN', $user->getRoles());
            case 'delete':
                return $username === $post->getAuthor() || in_array('ROLE_ADMIN', $user->getRoles());
        }

        return false;
    }
}