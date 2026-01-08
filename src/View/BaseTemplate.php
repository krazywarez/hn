<!doctype html>
<html lang="en">

<head>
    <title><?php echo $this->title; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="author" content="<?php echo $GLOBALS['author_name']; ?>">
    <meta name="description" content="<?php echo $this->description; ?>">
    <link rel="canonical" href="<?php echo $this->canonical_url; ?>">
    <link rel="stylesheet" href="/static/styles.min.css">
</head>

<body>
<main id="main">
    <nav class="links">
        <span><a href="/">Top</a> &middot; </span>
        <span><a href="/best/">Best</a> &middot;</span>
        <span><a href="/new/">New</a> &middot;</span>
        <span><a href="/ask/">Ask</a> &middot;</span>
        <span><a href="/show/">Show</a> &middot;</span>
        <span><a href="/job/">Job</a></span>
    </nav>
    <?php echo $this->content; ?>
</main>

<footer>
    <p><a href="https://git.cleberg.net/hn.git">Source Code</a></p>
    <p>Copyright &copy; 2023 - <?php echo $this->current_year; ?></p>
</footer>

</body>

</html>
