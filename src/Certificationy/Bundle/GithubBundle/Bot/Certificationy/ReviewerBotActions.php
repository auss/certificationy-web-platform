<?php
 /**
 * This file is part of the Certificationy web platform.
 * (c) Johann Saunier (johann_27@hotmail.fr)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Certificationy\Bundle\GithubBundle\Bot\Certificationy;

final class ReviewerBotActions
{
    const GIT_CLONE = 'git_clone';
    const CHECK = 'check';
    const PERSIST = 'persist';
    const CLEAN = 'remove_folder';
}
