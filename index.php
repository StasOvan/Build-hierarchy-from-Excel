<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function readExcel($filePath) {
    $spreadsheet = IOFactory::load($filePath);
    $data = $spreadsheet->getActiveSheet()->toArray();
    return $data;
}

function buildTree(array $elements, $parentId = 0) {
    $branch = [];

    foreach ($elements as $element) {
        if ($element[1] == $parentId) { // Если родитель совпадает
            $children = buildTree($elements, $element[0]);
            $branch[] = [
                'id' => $element[0],
                'parent' => $element[1],
                'text' => $element[2],
                'children' => $children // Добавляем детей к элементу
            ];
        }
    }

    return $branch;
}

function displayTree(array $tree) {
    echo '<ul>';
    foreach ($tree as $node) {
        echo '<li>';
        echo '<span class="toggle" onclick="toggle(this)">';
        if (!empty($node['children'])) {
            echo '<span class="toggle-symbol">+</span>'; // Символ +
        } else {
            echo '<span class="toggle-without-symbol"></span>'; // отступ, если нет детей
        }
        $text1 = htmlspecialchars($node['id']) . ' - ' . '<i>' . htmlspecialchars($node['text']) . '</i>';
        $text2 = htmlspecialchars($node['id']) . ' - ' . htmlspecialchars($node['text']);
        echo $text1 . "<span class='copy-button' onclick=\"copyToClipboard('{$text2}'); event.stopPropagation();\">копировать</span>";
        echo '</span>';
        if (!empty($node['children'])) {
            echo '<ul style="display:none;">'; // Скрываем детей по умолчанию
            displayTree($node['children']); // Рекурсивно отображаем детей
            echo '</ul>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Дерево узлов</title>
    <style>
        body {
            font-size: 20px;
            font-family: system-ui;
            margin: 0;
            padding: 0;
        }
        i {
            margin-left: 10px;
            color: #000;
        }
        ul {
            list-style-type: none; /* Убираем стандартные маркеры списка */
            padding-left: 20px; /* Отступ для вложенных списков */
            position: relative;
        }
        li {
            position: relative; /* Для позиционирования линий */
            padding-left: 20px; /* Отступ для текста узла */
        }
        .toggle {
            display: flex; /* Используем flex для выравнивания */
            align-items: center; /* Центрируем по вертикали */
            position: relative; /* Для позиционирования линии */
            color: #444;
        }
        .toggle:hover {
            background-color: aliceblue;
        }
        .toggle-symbol {
            display: inline-block;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            border: 1px solid red;
            color: red;
            margin-right: 5px;
            font-weight: bold;
            font-size: 16px;
            position: relative;
            cursor: pointer;
            padding: 0px 3px 3px 3px;
        }
        .toggle-without-symbol {
            margin-left: 34px;
        }
        .toggle-symbol:before {
            content: '';
            position: absolute;
            left: 50%;
            top: 20px; /* Начальная позиция линии */
            width: 1px;
            height: 20px; /* Длина линии */
            background: red; /* Цвет соединительной линии */
            transform: translateX(-50%);
            display: none; /* Скрываем по умолчанию */
        }
        li > .toggle:hover .toggle-symbol:before {
            display: none; /* Показываем линию при наведении */
        }
        li > ul {
            padding-left: 20px; /* Отступ для вложенных узлов */
        }
        li > ul:before {
            content: '';
            position: absolute;
            left: 14px; /* Начальная позиция линии */
            top: 0px; /* Высота линии */
            width: 1px;
            height: 98%; /* Длина линии до конца дочерних узлов */
            background: red; /* Цвет соединительной линии */
        }
        p {
            margin: 0 0 0 10px;
            padding: 0;
        }
        .copy-button {
            margin: 3px 20px 0 20px;
            font-size: 13px;
            padding: 0 10px 2px 10px;
            border: 1px solid #999;
            color: #999;
            cursor: pointer;
            background-color: #fff;
            border-radius: 12px;
        }
        .copy-button:hover {
            color: #000;
            background-color: #f2fff2;
        }
    </style>
    <script>
        function toggle(element) {
            const nextUl = element.parentNode.querySelector('ul');
            const symbol = element.querySelector('.toggle-symbol');
            if (nextUl) {
                if (nextUl.style.display === 'none') {
                    nextUl.style.display = 'block';
                    symbol.textContent = '-'; // Меняем на "-"
                } else {
                    nextUl.style.display = 'none';
                    symbol.textContent = '+'; // Меняем на "+"
                }
            }
        }
    </script>
</head>


<body>

<!-- ############ --><!-- ############ --><!-- ############ --><!-- ############ -->
<div style="background-color: #f3f3f3; padding: 10px; border-bottom: 1px solid #000;">
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file" accept=".xlsx" required>
    <button style="
    background-color: green;
    color: white;
    border: none;
    padding: 10px;" 
    submit">Загрузить данные</button>
</form>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка, был ли загружен файл
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        // Указываем имя файла
        $uploadFile = 'file.xlsx';

        // Перемещаем загруженный файл в текущую директорию
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            echo "<p>Файл успешно загружен</p>";
        } else {
            echo "Ошибка при загрузке файла.";
        }
    } else {
        echo "Ошибка: " . $_FILES['file']['error'];
    }
}
?>
<!-- ############ --><!-- ############ --><!-- ############ --><!-- ############ -->

<div style="text-align: center;">
    <button id="showLine" onclick="setStyle(300)">Режим 1</button>
    <button id="hideLine" onclick="setStyle(400)">Режим 2</button>
    <button id="hideLine" onclick="setStyle(500)">Режим 3</button>
</div>

<script>
    
    const style = document.createElement('style');
    style.textContent = `
        ul:before {
            content: none !important;
        }
    `;
    document.getElementById('hideLine').addEventListener('click', () => {
        document.head.appendChild(style); // Скрыть :before
    });
    document.getElementById('showLine').addEventListener('click', () => {
        if (document.head.contains(style)) {
            document.head.removeChild(style); // Показать :before
        }
    });
    

    function setStyle(weight) {
        const elements = document.querySelectorAll('i');
        const buttons = document.querySelectorAll('.copy-button');
        
        if (weight == 300) 
            buttons.forEach(button => { button.style.display = 'none';});
        if (weight == 400) 
            buttons.forEach(button => { button.style.display = 'none'; });
        if (weight == 500) 
            buttons.forEach(button => { button.style.display = 'block';});

        elements.forEach(element => {
            element.style.fontWeight = weight;
        });
    }
    function enableBeforePseudoElement() {
       document.head.removeChild(style);
    }
    function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Скопировано в буфер обмена: ' + text);
    }).catch(err => {
        console.error('Ошибка при копировании: ', err);
    });
}
</script>

<?php
$filePath = 'file.xlsx'; // Укажите путь к вашему файлу
$data = readExcel($filePath);
$tree = buildTree($data);
displayTree($tree);
?>

</body>
</html>
