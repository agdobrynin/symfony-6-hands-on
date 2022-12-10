<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Add new user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository     $userRepository,
        private readonly ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $user = (new User())->setEmail($email);
        $passwordHashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($passwordHashed);

        $errors = $this->validator->validate($user);

        if (count($errors)) {
            throw new \UnexpectedValueException((string)$errors);
        }

        $this->userRepository->save($user, true);

        $io->success(sprintf('User %s was added', $user->getEmail()));

        return Command::SUCCESS;
    }
}
