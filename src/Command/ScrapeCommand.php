<?php

namespace Scrapoutte\Command;

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeCommand extends Command
{
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';

    const DEFAULT_SELECTOR = 'a'; // Links

    protected function configure()
    {
        // Base configuration
        $this
          ->setName('scrape')
          ->setDescription('Scrapes a web page')
          ->setHelp('This command allows you to scrape a web page, to warm-up cache for instance');

        // Arguments & options
        $this
          ->addArgument('url', InputArgument::REQUIRED, 'URL')
          ->addOption('user-agent', 'u', InputOption::VALUE_OPTIONAL, 'User Agent string', self::DEFAULT_USER_AGENT)
          ->addOption('selector', 's', InputOption::VALUE_OPTIONAL, 'Selectors to click', self::DEFAULT_SELECTOR);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $goutteClient = new Client();
        $guzzleClient = new GuzzleClient(
          array(
            'timeout' => 60,
            'headers' => [
              'Accept-Encoding' => 'gzip, deflate, sdch, br',
            ],
          )
        );

        $goutteClient->setClient($guzzleClient);
        $goutteClient->setHeader('User-Agent', $input->getOption('user-agent'));

        $crawler = $goutteClient->request('GET', $input->getArgument('url'));

        $crawler->filter($input->getOption('selector'))->each(
          function ($node) use ($goutteClient, $output) {
              /** @var \Symfony\Component\DomCrawler\Link $link */
              $link = $node->link();
              $begin = microtime(true);
              $goutteClient->click($link);
              $duration = (microtime(true) - $begin) ;

              $output->writeln(sprintf('<comment>Scraped link "%s" [%s] in %.2fs</comment>', trim($node->text()), $link->getUri(), $duration));
          }
        );

        // TODO: show statistics
        $output->writeln('<info>All done!</info>');
    }
}
