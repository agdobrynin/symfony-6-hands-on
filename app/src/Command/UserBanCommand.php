<?php
declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user:ban',
    description: 'Ban user',
)]
class UserBanCommand extends \Symfony\Component\Console\Command\Command
{
    public function __construct(
        private readonly UserRepository     $userRepository,
        private readonly ValidatorInterface $validator,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command or unban allows ban user. If bannedDate is empty the user unbanned.');
        $this->addArgument('email', InputArgument::REQUIRED, 'User email');
        $this->addArgument('bannedUntil', InputArgument::OPTIONAL, 'Date until ban user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        if ($user = $this->userRepository->findOneBy(['email' => $email])) {
            $bannedUntil = $input->getArgument('bannedUntil');

            if ($bannedUntil) {
                $bannedUntil = new \DateTimeImmutable($input->getArgument('bannedUntil'));
                $confirmMessage = sprintf(
                    'Banned user %s until date %s ? [default: yes]: ',
                    $user->getEmail(),
                    $bannedUntil->format(\DateTimeInterface::ATOM)
                );
            } else {
                $confirmMessage = sprintf('Unban user %s ? [default: yes]: ', $user->getEmail());
            }

            $question = new ConfirmationQuestion($confirmMessage, true);
            $helper = $this->getHelper('question');

            if ($helper->ask($input, $output, $question)) {
                $user->setBannedUntil($bannedUntil);
                $this->userRepository->save($user, true);

                if ($bannedUntil) {
                    $successMessage = sprintf('User %s banned until date %s', $user->getEmail(), $bannedUntil->format(\DateTimeInterface::ATOM));
                } else {
                    $successMessage = sprintf('User %s unbanned', $user->getEmail());
                }

                $io->success($successMessage);
            }

            return Command::SUCCESS;
        } else {
            $io->warning(sprintf('User with email %s not found', $email));

            return Command::INVALID;
        }
    }
}
