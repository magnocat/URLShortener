<?php

// All relevant changes can be made in the data file. Please read the docs: https://github.com/flokX/devShort/wiki

$short = htmlspecialchars($_GET["short"]);
$return_404 = array("favicon.ico", "assets/vendor/bootstrap/bootstrap.min.css.map", "assets/vendor/frappe-charts/frappe-charts.min.iife.js.map");

// If the robots.txt is requested, return it
if ($short === "robots.txt") {
	header("Content-Type: text/plain; charset=utf-8");
	echo "User-agent: *\n";
	echo "Disallow: /\n";
	exit;
} else if (in_array($short, $return_404)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

// Counts the access to the given $name
function count_access($base_path, $name) {
    $filename = $base_path . DIRECTORY_SEPARATOR . "stats.json";
    $stats = json_decode(file_get_contents($filename), true);
    $stats[$name][mktime(0, 0, 0)] += 1;
    file_put_contents($filename, json_encode($stats, JSON_PRETTY_PRINT));
}

$base_path = implode(DIRECTORY_SEPARATOR, array(__DIR__, "admin"));
$config_content = json_decode(file_get_contents($base_path . DIRECTORY_SEPARATOR . "config.json"), true);

if (array_key_exists($short, $config_content["shortlinks"])) {
    header("Location: " . $config_content["shortlinks"][$short], $http_response_code=303);
    count_access($base_path, $short);
    exit;
} else if ($short === "") {
    header("Location: index.php", $http_response_code=301);
    exit;
} else {
    header("HTTP/1.1 404 Not Found");
    count_access($base_path, "404-request");

    // Generator for page customization
    $links_string = "";
    if ($config_content["settings"]["custom_links"]) {
        foreach ($config_content["settings"]["custom_links"] as $name => $url) {
            $links_string = $links_string . "<a href=\"$url\" class=\"badge badge-secondary\">$name</a> ";
        }
        $links_string = substr($links_string, 0, -1);
    }
}

?>

<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <meta name="author" content="<?php echo $config_content["settings"]["author"]; ?> and the devShort team">
    <link rel="icon" href="<?php echo $config_content["settings"]["favicon"]; ?>">
    <title>404 | <?php echo $config_content["settings"]["name"]; ?></title>
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="assets/main.css" rel="stylesheet">
</head>

<body class="d-flex flex-column h-100">

    <main role="main" class="flex-shrink-0">
        <div class="container">
            <nav class="mt-3" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo $config_content["settings"]["home_link"]; ?>">Home</a></li>
                    <li class="breadcrumb-item"><?php echo $config_content["settings"]["name"]; ?></li>
                    <li class="breadcrumb-item active" aria-current="page">404</li>
                </ol>
            </nav>
            <h1 class="mt-5">404 | Não encontrado.</h1>
            <p class="lead">O link encurtado <i><?php echo $short; ?></i> não foi encontrado neste servidor. Ele pode ter sido deletado, expirado, digitado errado ou foi comido por um monstro.</p>
        </div>
    </main>

    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">&copy; <?php echo date("Y") . " " . $config_content["settings"]["author"]; ?> and <a href="https://github.com/flokX/devShort">devShort</a></span>
                <?php if ($links_string) { echo "<span class=\"text-muted\">$links_string</span>"; } ?>
            </div>
        </div>
    </footer>

</body>

</html>
