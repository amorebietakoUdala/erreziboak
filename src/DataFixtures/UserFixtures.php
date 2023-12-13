<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends BaseFixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordEncoder)
    {
    }

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'main_users', function ($i) use ($manager) {
            $user = new User();
            $user->setUsername(sprintf('user%d', $i));
            $user->setEmail(sprintf('user%d@amorebieta.eus', $i));
            $user->setFirstName($this->faker->firstName);

            $user->setPassword($this->passwordEncoder->hashPassword(
                $user,
                'engage'
            ));

            return $user;
        });

        $this->createMany(3, 'admin_users', function ($i) {
            $user = new User();
            $user->setUsername(sprintf('admin%d', $i));
            $user->setEmail(sprintf('admin%d@amorebieta.eus', $i));
            $user->setFirstName($this->faker->firstName);
            $user->setRoles(['ROLE_ADMIN']);

            $user->setPassword($this->passwordEncoder->hashPassword(
                $user,
                'engage'
            ));

            return $user;
        });

        $manager->flush();
    }
}
