<?php

namespace HN\Controllers;

require_once 'src/Controller/FeedController.php';

use function HN\Models\GetApiResults;
use function HN\Models\ParseItem;
use function HN\Models\ParseStories;
use function HN\Models\ParseUser;

class RouteController
{
    /**
     * @var string
     */
    private string $request;

    public function __construct(string $request)
    {
        $this->request = $request;
    }

    /**
     * Route the user to the appropriate function, based on the URL
     *
     * @access public
     * @return void No return type; send user to FeedController->render() or a 404 error
     * @author Christian Cleberg <hello@cleberg.net>
     */
    public function routeUser(): void
    {
        include_once 'src/Model/ApiService.php';
        $path = ltrim($this->request, '/');
        $elements = explode('/', $path);

        switch (array_shift($elements)) {
            case '':
            case 'top':
                $feed = new FeedController(
                    $GLOBALS['full_domain'],
                    'The top stories from Hacker News, proxied by hn.',
                    'hn',
                    ParseStories(
                        GetApiResults(
                            'https://hacker-news.firebaseio.com/v0/topstories.json?limitToFirst=10&orderBy="$key"'
                        ),
                        'Top'
                    )
                );

                $feed->render();
                break;

            case 'best':
                $feed = new FeedController(
                    $GLOBALS['full_domain'] . '/Best/',
                    'The best stories from Hacker News, proxied by hn.',
                    'hn ~ best',
                    ParseStories(
                        GetApiResults(
                            'https://hacker-news.firebaseio.com/v0/beststories.json?limitToFirst=10&orderBy="$key"'
                        ),
                        'Best'
                    )
                );

                $feed->render();
                break;

            case 'new':
                $feed = new FeedController(
                    $GLOBALS['full_domain'] . '/new/',
                    'The newest stories from Hacker News, proxied by hn.',
                    'hn ~ new',
                    ParseStories(
                        GetApiResults(
                            'https://hacker-news.firebaseio.com/v0/newstories.json?limitToFirst=10&orderBy="$key"'
                        ),
                        'New'
                    )
                );

                $feed->render();
                break;

            case 'ask':
                $feed = new FeedController(
                    $GLOBALS['full_domain'] . '/ask/',
                    'The top asks from Hacker News, proxied by hn.',
                    'hn ~ ask',
                    ParseStories(
                        GetApiResults(
                            'https://hacker-news.firebaseio.com/v0/askstories.json?limitToFirst=10&orderBy="$key"'
                        ),
                        'Ask'
                    )
                );

                $feed->render();
                break;

            case 'show':
                $feed = new FeedController(
                    $GLOBALS['full_domain'] . '/show/',
                    'The latest showcases from Hacker News, proxied by hn.',
                    'hn ~ show',
                    ParseStories(
                        GetApiResults(
                            'https://hacker-news.firebaseio.com/v0/showstories.json?limitToFirst=10&orderBy="$key"'
                        ),
                        'Show'
                    )
                );

                $feed->render();
                break;

            case 'job':
                $feed = new FeedController(
                    $GLOBALS['full_domain'] . '/job/',
                    'The latest jobs from Hacker News, proxied by hn.',
                    'hn ~ jobs',
                    ParseStories(
                        GetApiResults(
                            'https://hacker-news.firebaseio.com/v0/jobstories.json?limitToFirst=10&orderBy="$key"'
                        ),
                        'Job'
                    )
                );

                $feed->render();
                break;

            case 'user':
                $feed = new FeedController(
                    $GLOBALS['full_domain'] . '/user/' . $elements[0],
                    'The Hacker News profile for ' . $elements[0] . ', proxied by hn.',
                    'hn ~ ' . $elements[0],
                    ParseUser(
                        GetApiResults(
                            'https://hacker-news.firebaseio.com/v0/user/' . $elements[0] . '.json'
                        ),
                        'User: ' . $elements[0]
                    )
                );

                $feed->render();
                break;

            case 'item':
                $feed = new FeedController(
                    $GLOBALS['full_domain'] . '/item/' . $elements[0],
                    'Hacker News story ' . $elements[0] . ', proxied by hn.',
                    'hn ~ ' . $elements[0],
                    ParseItem(
                        GetApiResults(
                            'https://hacker-news.firebaseio.com/v0/item/' . $elements[0] . '.json'
                        )
                    )
                );

                $feed->render();
                break;

            default:
                header('HTTP/1.1 404 Not Found');
        }
    }
}