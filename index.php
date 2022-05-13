<?php
function exec_time_wrapper($callback, ...$args) {
  $exec_time_start = microtime(true);

  $callback(...$args);

  $exec_time_end = microtime(true);
  $exec_total_time = floor(($exec_time_end - $exec_time_start) * 1000);

  echo "<p><b>Execution Time:</b> $exec_total_time ms</p>";
};

function read_csv() {
  $row = 1;
  $url;
  $json_data = array();
  $generated_links = array();

  if (($handle = fopen('raw_db.csv', 'r')) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
      $num = count($data);
      $url = gen_url($url);

      $generated_links[] = array('link' => $url, 'data' => $data[0]);

      $row++;
    }

    $json_data['links'] = $generated_links;

    write_to_json($json_data);

    echo "<p><b>Number of generated urls:</b> $row </p>\n\n";
    fclose($handle);
  }
};

function read_json($get_link) {
  $json_string = file_get_contents('short_links.json');
  $parsed_json = json_decode($json_string, true);
  $link_found = false;

  foreach ($parsed_json['links'] as $value) {
    $link = $value['link'];
    $data = $value['data'];

    if ($get_link === $link) {
      $link_found = true;
      echo "<p><b>Data found:</b> $data</p><br/>";
    }
  }

  if (!$link_found) {
    echo "<p><b>Data not found for code:</b> $get_link</p><br/>";
  }
}

function gen_url($prev_url) {
  $characters = '0123456789bdfghijklmnqrstuvwzDFGHIJLNQRSUVWYZ';
  $characters_length = strlen($characters);
  $url = '';

  // Первичная генерация короткой ссылки
  for ($i = 0; $i < 6; $i++) {
    $url .= $characters[rand(0, $characters_length - 1)];
  }

  // Убеждаемся что она не повторяется с предыдущей, переданной в качестве аргумента
  while ($prev_url === $url) {
    $url = '';

    for ($i = 0; $i < 6; $i++) {
      $url .= $characters[rand(0, $characters_length - 1)];
    }
  }

  return $url;
}

function write_to_json($data) {
  $fp = fopen('short_links.json', 'w');
  fwrite($fp, json_encode($data));
  fclose($fp);
}

/*
  ******** Блок ниже отвечает за навигацию по функицям приложения
*/

if ($_GET["gen"] === 'true') {
  echo exec_time_wrapper('read_csv');
}

if($_GET["code"]) {
  echo exec_time_wrapper('read_json', $_GET["code"]);
}
?>