<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Add new user',
)]
class UserCreateCommand extends Command
{
    private const PASSWORD_MIN_LENGTH = 6;

    public function __construct(
        private readonly UserRepository     $userRepository,
        private readonly ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
        private ParameterBagInterface       $parameterBag
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $question = new Question('Please enter user email: ');

        $question->setValidator(function ($answer) use ($io) {
            $errors = $this->validator->validate(
                $answer, [
                new Assert\Email(),
                new Assert\NotBlank(),
                new Assert\NotNull(),
            ]);

            if (count($errors)) {
                throw new \UnexpectedValueException((string)$errors);
            }

            if ($this->userRepository->findOneBy(['email' => $answer])) {
                throw new \RuntimeException(sprintf('Email %s already exist. Choose another email', $answer));
            }

            return $answer;
        });

        $question->setMaxAttempts(2);

        $email = $helper->ask($input, $output, $question);

        $strQuestion = sprintf('Please enter user password (min %s symbols): ', self::PASSWORD_MIN_LENGTH);
        $question = new Question($strQuestion);
        $question->setHidden(true)->setHiddenFallback(false);

        $question->setValidator(static function ($answer) {
            if (!is_string($answer) || mb_strlen($answer) < self::PASSWORD_MIN_LENGTH) {
                throw new \RuntimeException(sprintf('Password must be more then %s symbols', self::PASSWORD_MIN_LENGTH));
            }

            return $answer;
        });

        $question->setMaxAttempts(2);

        $password1 = $helper->ask($input, $output, $question);

        $question = new Question('Please retype user password: ');
        $question->setHidden(true)->setHiddenFallback(false);
        $password2 = $helper->ask($input, $output, $question);

        if ($password1 !== $password2) {
            throw new \UnexpectedValueException('Passwords mast matched!');
        }

        $confirmMessage = sprintf('Set email %s as verified? [default yes]: ', $email);
        $question = new ConfirmationQuestion($confirmMessage, true);
        $verified = $helper->ask($input, $output, $question);

        $roles = array_reverse(array_keys($this->parameterBag->get('security.role_hierarchy.roles')));

        $question = new ChoiceQuestion(
            'Please select user role (defaults to ROLE_USER): ',
            ['ROLE_USER', ...$roles],
            0
        );
        $question->setErrorMessage('Role %s is invalid.');
        $role = $helper->ask($input, $output, $question);

        $confirmMessage = sprintf('Create new user with email %s and role %s and verified %s? [default yes]: ', $email, $role, ($verified ? 'yes' : 'no'));
        $question = new ConfirmationQuestion($confirmMessage, true);

        if (!$helper->ask($input, $output, $question)) {
            $io->info('You cancel add new user.');
            return Command::SUCCESS;
        }

        $user = (new User())->setEmail($email);
        $passwordHashed = $this->passwordHasher->hashPassword($user, $password1);
        $user->setPassword($passwordHashed);
        $user->setIsVerified($verified);

        $errors = $this->validator->validate($user);

        if (count($errors)) {
            throw new \UnexpectedValueException((string)$errors);
        }

        $this->userRepository->save($user, true);

        $io->success(sprintf('User %s was added', $user->getEmail()));

        return Command::SUCCESS;
    }
}
