<?php

namespace HN\Controllers;

class FeedController
{
    /**
     * @var string
     */
    private string $canonical_url;
    /**
     * @var string
     */
    private string $description;
    /**
     * @var string
     */
    private string $title;
    /**
     * @var string
     */
    private string $content;
    /**
     * @var false|string
     */
    private mixed $current_year;

    public function __construct(string $canonical_url, string $description, string $title, string $content)
    {
        $this->canonical_url = $canonical_url;
        $this->description = $description;
        $this->title = $title;
        $this->content = $content;
        $this->current_year = date("Y");
    }

    /**
     * Request template to be presented to the user
     *
     * @access public
     * @author Christian Cleberg <hello@cleberg.net>
     */
    public function render(): void
    {
        include_once 'src/View/BaseTemplate.php';
    }
}
