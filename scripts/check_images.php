<?php

foreach (['airplane_real.png', 'cloud_real_1.png', 'cloud_real_2.png', 'cloud_real_3.png'] as $name) {
    $path = 'public/images/' . $name;
    $info = getimagesize($path);
    echo $name . ': ' . $info[0] . 'x' . $info[1] . ' mime=' . $info['mime'] . PHP_EOL;
}
