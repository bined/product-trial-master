<?php

namespace App\Story;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class DefaultUserStory extends Story
{
    public function build(): void
    {
       UserFactory::createMany(100);
    }
}
