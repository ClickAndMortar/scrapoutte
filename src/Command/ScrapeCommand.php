<?php

namespace Scrapoutte\Command;

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Scrapoutte\Helper\UrlHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeCommand extends Command
{
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';

    const DEFAULT_SELECTOR = 'a'; // Links

    /**
     * @var array
     */
    protected $visitedLinks = [];

    /**
     * @var UrlHelper
     */
    protected $urlHelper = null;

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

        $this->urlHelper = new UrlHelper();
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

        $selector = $input->getOption('selector');
        $baseUrl = $input->getArgument('url');
        $output->writeln(sprintf('<info>Begining scrape of elements matching [%s] on [%s]</info>', $baseUrl, $selector));
        $output->writeln('');
        $crawler = $goutteClient->request('GET', $baseUrl);

        $crawler->filter($selector)->each(
          function ($node) use ($goutteClient, $output, $baseUrl) {
              /** @var \Symfony\Component\DomCrawler\Link $link */
              $link = $node->link();
              $uri = $link->getUri();

              if ($this->isUrlScraped($uri) || !$this->urlHelper->isSameHost($uri, $baseUrl)) {
                  return;
              }

              $begin = microtime(true);
              $goutteClient->click($link);
              $duration = (microtime(true) - $begin);
              $this->markUrlAsScraped($uri, $duration);

              $output->writeln(sprintf('<comment>Scraped link "%s" [%s] in %.2fs</comment>', trim($node->text()),
                $link->getUri(), $duration));
          }
        );

        $this->showStatistics($output);

        $output->writeln('<info>All done!</info>');
    }

    /**
     * Check if URL has already been scraped
     *
     * @param $url
     * @return bool
     */
    protected function isUrlScraped($url)
    {
        return array_key_exists($this->urlHelper->getUrlHash($url), $this->visitedLinks);
    }

    /**
     * Mark URL as scraped
     *
     * @param string $url URL
     * @param float $duration In seconds
     *
     * @return void
     */
    protected function markUrlAsScraped($url, $duration)
    {
        $this->visitedLinks[$this->urlHelper->getUrlHash($url)] = [
          'url' => $url,
          'duration' => $duration,
        ];
    }

    /**
     * Show statistics
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function showStatistics(OutputInterface $output)
    {
        $output->writeln('');
        $table = new Table($output);
        $table->setHeaders(array('URL', 'Duration (s)'));

        foreach ($this->visitedLinks as $visitedLink) {
            $table->addRow(array($visitedLink['url'], round($visitedLink['duration'], 2)));
        }

        $table
          ->addRow(new TableSeparator())
          ->addRow(array('Total URLs scraped', 'Total duration (s)'))
          ->addRow(new TableSeparator())
          ->addRow(array(count($this->visitedLinks), round(array_sum(array_column($this->visitedLinks, 'duration')), 2)));

        $table->render();
    }
}
