<?php

// All relevant changes can be made in the data file. Please read the docs: https://github.com/flokX/devShort/wiki

$config_path = __DIR__ . DIRECTORY_SEPARATOR . "config.json";
$config_content = json_decode(file_get_contents($config_path), true);
$stats_path = __DIR__ . DIRECTORY_SEPARATOR . "stats.json";
$stats_content = json_decode(file_get_contents($stats_path), true);

// Filter the names that the admin interface doesn't break
function filter_name($nameRaw) {
    $name = filter_var($nameRaw, FILTER_SANITIZE_STRING);
    $name = str_replace(" ", "-", $name);
    $name = preg_replace("/[^A-Za-z0-9-_]/", "", $name);
    return $name;
}

// API functions to delete and add the shortlinks via the admin panel
if (isset($_GET["delete"]) || isset($_GET["add"])) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($_GET["delete"])) {
        unset($config_content["shortlinks"][$data["name"]]);
        unset($stats_content[$data["name"]]);
    } else if (isset($_GET["add"])) {
        $filtered = array("name" => filter_name($data["name"]),
                          "url" => filter_var($data["url"], FILTER_SANITIZE_URL));
        if (!filter_var($filtered["url"], FILTER_VALIDATE_URL)) {
            echo "{\"status\": \"unvalid-url\"}";
            exit;
        }
        $config_content["shortlinks"][$filtered["name"]] = $filtered["url"];
        $stats_content[$filtered["name"]] = array();
    }
    file_put_contents($config_path, json_encode($config_content, JSON_PRETTY_PRINT));
    file_put_contents($stats_path, json_encode($stats_content, JSON_PRETTY_PRINT));
    echo "{\"status\": \"successful\"}";
    exit;
}

// Generator for page customization
$links_string = "";
if ($config_content["settings"]["custom_links"]) {
    foreach ($config_content["settings"]["custom_links"] as $name => $url) {
        $links_string = $links_string . "<a href=\"$url\" class=\"badge badge-secondary\">$name</a> ";
    }
    $links_string = substr($links_string, 0, -1);
}

?>

<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <meta name="author" content="<?php echo $config_content["settings"]["author"]; ?> and the devShort team">
    <link rel="icon" href="../<?php echo $config_content["settings"]["favicon"]; ?>">
    <title>Painel | <?php echo $config_content["settings"]["name"]; ?></title>
    <link href="../assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-7769385014567112",
          enable_page_level_ads: true
     });
</script>
</head>

<body class="d-flex flex-column h-100">

    <main role="main" class="flex-shrink-0">
        <div class="container">
            <h1 class="mt-5 text-center"><?php echo $config_content["settings"]["name"]; ?></h1>
            <h4 class="mb-4 text-center">Painel Administrativo</h4>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Link <small><a id="refresh" href="#refresh" class="card-link">Atualizar</a></small></h5>
                    <form class="form-inline" id="add-form">
                        <label class="sr-only" for="name">Nome</label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="name" placeholder="Link1" required>
                        <label class="sr-only" for="url">URL (destination)</label>
                        <input type="url" class="form-control mb-2 mr-sm-2" id="url" placeholder="https://example.com" value="https://" required>
                        <button type="submit" class="btn btn-primary mb-2">Add</button>
                        <div id="status"></div>
                    </form>
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <div id="spinner" class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
            <div id="charts"></div>
            <p class="text-center mt-4 mb-5">powered by <a href="https://github.com/flokX/devShort">devShort</a> v2.2.1 (Latest: <a href="https://github.com/flokX/devShort/releases"><img src="https://img.shields.io/github/release/flokX/devShort.svg" alt="Latest release"></a>, <a href="https://github.com/flokX/devShort/wiki/Installation#update-or-reinstallation">How to update</a>)</p>
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

    <script src="../assets/vendor/frappe-charts/frappe-charts.min.iife.js"></script>
    <script src="main.js"></script>

</body>

</html>
