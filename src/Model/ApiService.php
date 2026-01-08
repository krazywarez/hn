<?php

namespace HN\Models;

/**
 * Extract a set of stories from the Hacker News API
 *
 * @access public
 * @param string $api_url The API endpoint to use for extraction
 * @return mixed The API results formatted into an HTML section
 * @author Christian Cleberg <hello@cleberg.net>
 */
function GetApiResults(string $api_url): mixed
{
    $response = file_get_contents($api_url);
    return json_decode($response, true);
}

/**
 * Formats a given set of API results into an HTML section
 *
 * @access public
 * @param mixed $api_results The decoded API results
 * @param string $inline_title The <h1> title to use in the HTML
 * @return string $html_output The formatted HTML result of stories from the API or the error message
 * @author Christian Cleberg <hello@cleberg.net>
 */
function ParseStories(mixed $api_results, string $inline_title): string
{
    if ($api_results == "null") {
        return '<p>ERROR: Stories not found. API returned `null`.</p>';
    } else {
        $html_output = '<h1>' . $inline_title . '</h1>';
        for ($i = 0; $i < count($api_results); $i++) {
            $story_api_results = GetApiResults('https://hacker-news.firebaseio.com/v0/item/' . $api_results[$i] . '.json');
            $html_output .= ConstructStory($story_api_results);
        }

        return $html_output;
    }
}

/**
 *Extract a user's profile from Hacker News API and format in HTML
 *
 * @access public
 * @param mixed $api_results The decoded API results
 * @param string $inline_title The <h1> title to use in the HTML
 * @return string $html_output The formatted HTML result of stories from the API
 * @author Christian Cleberg <hello@cleberg.net>
 */
function ParseUser(mixed $api_results, string $inline_title): string
{
    if ($api_results == "null") {
        return '<p>ERROR: User not found.</p>';
    } else {
        // TODO: Create function to format $about using the following guidelines
        //     : https://news.ycombinator.com/formatdoc
        //     : hint: nl2br() will solve the first formatting requirement
        $about = $api_results['about'];
        $karma = $api_results['karma'];
        $created = date('Y-m-d h:m:s', $api_results['created']);

        $html_output = <<<EOT
            <div class="user-details">
                <h1>$inline_title</h1>
                <p>About: $about</p>
                <p>Karma: $karma</p>
                <p>Created: <time datetime="$created">$created</time></p>
                <br>
                <h2>Recently Submitted</h2>
            </div>
        EOT;

        $limit = (count($api_results['submitted']) > 10) ? 10 : count($api_results['submitted']);
        if (count($api_results['submitted']) > 0) {
            for ($i = 0; $i < $limit; $i++) {
                $user_api_results = GetApiResults('https://hacker-news.firebaseio.com/v0/item/' . $api_results['submitted'][$i] . '.json');
                $html_output .= GetItem($user_api_results);
            }
        } else {
            $html_output .= '<p>User has no submissions.</p>';
        }

        return $html_output;
    }
}


/**
 * Formats one specific item requested by the user
 *
 * @access public
 * @param mixed $api_results The decoded API results
 * @param string $inline_title The <h1> title to use in the HTML
 * @return string $html_output The formatted HTML result of stories from the API or the error message
 * @author Christian Cleberg <hello@cleberg.net>
 */
function ParseItem(mixed $api_results): string
{
    if ($api_results == "null") {
        return '<p>ERROR: User not found.</p>';
    } else {
        $html_output = '';

        if (in_array($api_results['type'], array("job", "story", "poll", "pollopt"))) {
            $html_output .= ConstructStoryDiscussion($api_results);
        } else {
            if (array_key_exists('parent', $api_results)) {
                $parent_api_results = GetApiResults('https://hacker-news.firebaseio.com/v0/item/' . $api_results['parent'] . '.json');
                $html_output .= GetItem($parent_api_results);
                $html_output .= '<hr>';
            }

            $html_output .= GetItem($api_results);

            if ($api_results['descendants'] != 0) {
                $html_output .= '<hr>';
                $child_api_results = GetApiResults('https://hacker-news.firebaseio.com/v0/item/' . $api_results['kids'][0] . '.json');
                $html_output .= GetItem($child_api_results);
            }
        }

        return $html_output;
    }
}


/**
 * Formats one item from the API to HTML
 *
 * @access public
 * @param mixed $api_results The decoded API results
 * @return string The formatted HTML result of stories from the API or the error message
 * @author Christian Cleberg <hello@cleberg.net>
 */
function GetItem(mixed $api_results): string
{
    $type = $api_results['type'];

    return match ($type) {
        'story', 'job' => ConstructStory($api_results),
        'comment' => ConstructComment($api_results),
        'poll' => ConstructPoll($api_results),
        'pollopt' => ConstructPollOpt($api_results),
        default => '[ERROR] Item type not found: ' . $type,
    };
}


/**
 * Creates a story HTML element
 *
 * @access public
 * @param mixed $api_results The decoded API results
 * @return string The formatted HTML result of stories from the API or the error message
 * @author Christian Cleberg <hello@cleberg.net>
 */
function ConstructStory(mixed $api_results): string
{
    $id = $api_results['id'];
    $url = $api_results['url'];
    $title = $api_results['title'];
    $time = date('Y-m-d h:m:s', $api_results['time']);
    $by = $api_results['by'];
    $score = $api_results['score'];
    if (array_key_exists('descendants', $api_results)) {
        $descendants = $api_results['descendants'];
    } else {
        $descendants = 'No';
    }

    return <<<EOT
        <div class="story">
            <a href="$url">$title</a>
            <p>
                <time datetime="$time">$time</time>
                by <a href="/user/$by/">$by</a>
                | $score points
                | <a href="/item/$id">$descendants comments</a>
            </p>
        </div>
    EOT;
}

/**
 * Creates a story discussion page with comments
 *
 * @access public
 * @param mixed $api_results The decoded API results
 * @return string The formatted HTML result of stories from the API or the error message
 * @author Christian Cleberg <hello@cleberg.net>
 */
function ConstructStoryDiscussion(mixed $api_results): string
{
    $id = $api_results['id'];
    $url = $api_results['url'];
    $title = $api_results['title'];
    $time = date('Y-m-d h:m:s', $api_results['time']);
    $by = $api_results['by'];
    $score = $api_results['score'];
    $descendants = $api_results['descendants'];
    if (array_key_exists('text', $api_results)) {
        $text = $api_results['text'];
    } else {
        $text = '';
    }

    $html_output = <<<EOT
        <div class="story-discussion">
            <h1><a href="$url" target="_blank" rel="noopener">$title</a></h1>
            <p>
                <time datetime="$time">$time</time>
                by <a href="/user/$by/">$by</a>
                | $score points
                | <a href="/item/$id">$descendants comments</a>
            </p>
            <p>$text</p>
        </div>
    EOT;

    // TODO: Add support for more than just top-level kids (i.e., recursive).
    if ($api_results['descendants'] != 0) {
        $html_output .= <<<EOT
            <div class="story-discussion-comments">
                <h2>Comments</h2>
        EOT;
        for ($i = 0; $i < count($api_results['kids']); $i++) {
            $child_api_results = GetApiResults('https://hacker-news.firebaseio.com/v0/item/' . $api_results['kids'][$i] . '.json');
            $html_output .= GetItem($child_api_results);
        }
        $html_output .= '</div>';
    }

    return $html_output;
}

/**
 * Creates a comment HTML element
 *
 * @access public
 * @param mixed $api_results The decoded API results
 * @return string The formatted HTML result of stories from the API or the error message
 * @author Christian Cleberg <hello@cleberg.net>
 */
function ConstructComment(mixed $api_results): string
{
    $time = date('Y-m-d h:m:s', $api_results['time']);
    $text = $api_results['text'];
    $by = $api_results['by'];
    $parent = $api_results['parent'];

    return <<<EOT
        <div class="comment">
            <p>$text</p>
            <p><i>Submitted in response to: <a href="/item/$parent/">$parent</a></i></p>
            <p><time datetime="$time">$time</time> by <a href="/user/$by/">$by</a></p>
        </div>
    EOT;
}

/**
 * Creates a poll HTML element
 *
 * @access public
 * @param mixed $api_results The decoded API results
 * @return string The formatted HTML result of stories from the API or the error message
 * @author Christian Cleberg <hello@cleberg.net>
 */
function ConstructPoll(mixed $api_results): string
{
    return 'TODO';
}

/**
 * Creates a poll-option HTML element
 *
 * @access public
 * @param mixed $api_results The decoded API results
 * @return string The formatted HTML result of stories from the API or the error message
 * @author Christian Cleberg <hello@cleberg.net>
 */
function ConstructPollOpt(mixed $api_results): string
{
    return 'TODO';
}
