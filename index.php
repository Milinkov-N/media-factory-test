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
      $url = gen_url($data[0]);

      $generated_links[] = array('link' => $url, 'data' => $data[0]);

      $row++;
    }

    $json_data['links'] = $generated_links;

    write_to_json($json_data);

    echo "<p><b>Number of generated urls:</b> $row </p>\n\n";
    fclose($handle);
  }
};

function find_data($get_link) {
  $row = 1;
  $link_found = false;

  if (($handle = fopen('raw_db.csv', 'r')) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
      if ($get_link === gen_url($data[0])) {
        $link_found = true;
        echo "<p><b>Data found:</b> $data[0]</p><br/>";
      }

      $row++;
    }

    fclose($handle);
  }

  if (!$link_found) {
    echo "<p><b>Data not found for code:</b> $get_link</p><br/>";
  }
}

function gen_url($str) {
  $hash_array = array();
  $hash = '';
  $characters = '0123456789bdfghijklmnqrstuvwzDFGHIJLNQRSUVWYZ';
  $size = 6;

  // Пробегаемся по всей переданной сроке
  for ($i = 0; $i < strlen($str); $i++) {
    // И перезаписываем значения в массиве хэша,
    // пока не пройдемся по всей строке
    // Это нужно для предсказуемой и уникальной
    // генерации короткой ссылки
    for ($j = 0; $j < $size; $j++) {
        $hash_array[$j] = ($hash_array[$j] + ord($str[$i]) + $j + $i + $size) % strlen($characters);
    }
  }

  for ($i = 0; $i < $size; $i++) {
    $hash .= $characters[$hash_array[$i]];
  }

  return $hash;
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
  echo exec_time_wrapper('find_data', $_GET["code"]);
}
?>