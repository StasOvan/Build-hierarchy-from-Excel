<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="styles.css">
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

<div class="background-image"></div>
<button id="scrollToTop" class="scrollToTop">Вверх</button>

<div class="header">
    <div class="header-main">
        <div style="text-align: center;">
            <!--button id="showLine" onclick="showLine">Линии</button-->
            <button id="showLine">Скрыть линии</button>
            <button id="showCopy">Скрыть кнопки "Копировать"</button>
            <button id="showGroup">Скрыть кнопки по группам</button>
        </div>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="file" accept=".xlsx" required>
            <button style="
                background-color: green;
                color: white;
                border: none;
                padding: 7px 17px;
                margin-right: 20px;
                cursor: pointer;
                border-radius: 5px;
            " 
            submit">Загрузить данные</button>
        </form>
    </div>
    <div class="header-search">
        <input type="text" id="searchInput" placeholder="Найти...">
        <span id="quantity-search" style="font-size: 14px;"></span>
        <button id="clearButton" style="
            background-color: #df2a2a;
            color: white;
            border: none;
            padding: 0 23px;
            margin-right: 20px;
            cursor: pointer;
            border-radius: 5px;
        ">
                Сбросить
        </button>
    </div>    
</div>

<script>

    const style = document.createElement('style');
    style.textContent = `ul:before {content: none !important;}`;
    document.getElementById('showLine').addEventListener('click', () => {
        if (document.getElementById('showLine').innerText == "Показать линии") {
            document.getElementById('showLine').innerText = "Скрыть линии";
            if (document.head.contains(style)) document.head.removeChild(style); // Показать :before
        } else {
            document.getElementById('showLine').innerText = "Показать линии";
            document.head.appendChild(style); // Скрыть :before
        }    
    });
    
    document.getElementById('showCopy').addEventListener('click', () => {
        const buttons = document.querySelectorAll('.copy-button');
        if (document.getElementById('showCopy').innerText == 'Скрыть кнопки "Копировать"') {
            document.getElementById('showCopy').innerText = 'Показать кнопки "Копировать"';
            buttons.forEach(button => { button.style.display = 'none';});
        } else {
            document.getElementById('showCopy').innerText = 'Скрыть кнопки "Копировать"';
            buttons.forEach(button => { button.style.display = 'block';});
        }
    });

    document.getElementById('showGroup').addEventListener('click', () => {
        const toggles = document.querySelectorAll('.toggle');
        if (document.getElementById('showGroup').innerText == 'Скрыть кнопки по группам') {
            toggles.forEach(toggle => { if (toggle.getElementsByTagName("button")[0]) toggle.getElementsByTagName("button")[0].style.display = "none"; });
            document.getElementById('showGroup').innerText = 'Показать кнопки по группам';
        } else {
            toggles.forEach(toggle => { if (toggle.getElementsByTagName("button")[0]) toggle.getElementsByTagName("button")[0].style.display = "block"; });
            document.getElementById('showGroup').innerText = 'Скрыть кнопки по группам';
        }
    });


    document.getElementById('searchInput').addEventListener('input', function() {
        const searchValue = this.value.trim().toLowerCase(); // Убираем пробелы и переводим в нижний регистр
        if (searchValue == '') {
            document.getElementById("quantity-search").innerText = "";
            return;
        }
        const items = document.querySelectorAll('.toggle');
        const item_0_toggleSymbol = items[0].getElementsByClassName('toggle-symbol')[0];
        const item_0_button = items[0].getElementsByTagName('button')[0];
        if (item_0_toggleSymbol.innerText == "+") item_0_button.click();
        if (item_0_toggleSymbol.innerText == "-") item_0_button.click();
        if (item_0_toggleSymbol.innerText == "+") item_0_button.click();

        var i = 0;
        items.forEach(item => {
            // Проверяем, содержится ли текст поиска в тексте элемента
            if (item.textContent.toLowerCase().includes(searchValue) && searchValue) {
                if (item.getElementsByClassName('toggle-symbol')[0] && item.getElementsByClassName('toggle-symbol')[0].innerText == "+") item.getElementsByClassName('toggle-symbol')[0].click();
                item.style.color = '#00c100';
                i++;
            } else {
                item.style.color = ''; // Сброс цвета
            }
        });
        document.getElementById("quantity-search").innerText = "Найдено совпадений: " + i;


        const elements = document.querySelectorAll('*');

        for (let element of elements) {
            const color = window.getComputedStyle(element).color;
            if (color === 'rgb(0, 193, 0)') { // соответствует #00c100
                window.scrollTo({ 
                    top: element.getBoundingClientRect().top + window.scrollY - 130,
                    behavior: 'smooth' });
                break; // Выходим из цикла после первого найденного элемента
            }
        }
    });


    // Обработчик для кнопки "Очистить"
    document.getElementById('clearButton').addEventListener('click', function() {
        document.getElementById('searchInput').value = ''; // Очищаем поле ввода
        document.getElementById("quantity-search").innerText = '';
        const items = document.querySelectorAll('.toggle');
        items.forEach(item => {
            item.style.color = ''; // Сброс цвета для всех элементов
        });
    });

</script>

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



<script>

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification(text);
        }).catch(err => {
            showNotification('Ошибка при копировании: ' + err);
            console.error('Ошибка при копировании: ', err);
        });
    }

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.innerHTML = '<span style="font-size: 12px;">Скопировано в буфер обмена:</span><br>' + message;
        notification.style.position = 'fixed';
        notification.style.top = '100px';
        notification.style.left = '50%';
        notification.style.transform = 'translateX(-50%)';
        notification.style.backgroundColor = '#FFF';
        notification.style.color = 'green';
        notification.style.padding = '0px 20px 5px 20px';
        notification.style.borderRadius = '5px';
        notification.style.opacity = '1';
        notification.style.transition = 'opacity 1.7s';
        notification.style.boxShadow = '0px 3px 5px 0px #a9a9a9';
        notification.style.fontSize = '20px';
        notification.style.textAlign = 'center';
        notification.style.zIndex = '1000';
        
        document.body.appendChild(notification);

        // Плавное появление
        setTimeout(() => {
            notification.style.opacity = '1';
        }, 100);

        // Убираем уведомление через 2 секунды
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 700); // Время, равное времени перехода
        }, 1500);
    }
</script>

<div class="content">
<?php
    $data = readExcel('file.xlsx');
    $tree = buildTree($data); 
    displayTree($tree); 
?>
</div>

<script>

    function toggle(element) {
        const nextUl = element.nextElementSibling;
        if (nextUl) {
            nextUl.style.display = nextUl.style.display === 'none' ? 'block' : 'none';
            const symbol = element.querySelector('.toggle-symbol');
            if (symbol) {
                symbol.innerText = symbol.innerText === '+' ? '-' : '+';
            }
        }
    }

    function addExpandButton() {
        const toggles = document.querySelectorAll('.toggle');
        toggles.forEach(toggle => {
            const nextUl = toggle.nextElementSibling;
            if (nextUl && nextUl.tagName === 'UL' && nextUl.children.length > 0) {
                const expandButton = document.createElement('button');
                expandButton.innerText = 'Раскрыть всю группу';
                expandButton.onclick = function(event) {
                    event.stopPropagation(); // Останавливаем всплытие события
                    toggleExpandCollapse(nextUl, expandButton, toggle);
                };
                toggle.appendChild(expandButton);
            }
        });
    }

    function toggleExpandCollapse(ul, button, toggleElement) {
        const isExpanded = ul.style.display === 'block';

        if (isExpanded) {
            ul.style.display = 'none';
            button.innerText = 'Раскрыть всю группу';
            const toggles = ul.querySelectorAll('.toggle');
            toggles.forEach(toggle => {
                const nextUl = toggle.nextElementSibling;
                if (nextUl) {
                    nextUl.style.display = 'none'; // Скрываем все дочерние списки
                    const symbol = toggle.querySelector('.toggle-symbol');
                    if (symbol) {
                        symbol.innerText = '+'; // Меняем символ на плюс
                    }
                }
            });
            const symbol = toggleElement.querySelector('.toggle-symbol');
            if (symbol) {
                symbol.innerText = '+'; // Меняем символ на плюс для родительского узла
            }
        } else {
            ul.style.display = 'block';
            button.innerText = 'Скрыть всю группу'; // Изменяем текст кнопки на "Скрыть узлы"
            const toggles = ul.querySelectorAll('.toggle');
            toggles.forEach(toggle => {
                const nextUl = toggle.nextElementSibling;
                if (nextUl) {
                    nextUl.style.display = 'block'; // Раскрываем все дочерние списки
                    const symbol = toggle.querySelector('.toggle-symbol');
                    if (symbol) {
                        symbol.innerText = '-'; // Меняем символ на минус
                    }
                }
            });
            const symbol = toggleElement.querySelector('.toggle-symbol');
            if (symbol) {
                symbol.innerText = '-'; // Меняем символ на минус для родительского узла
            }
        }

    }
    
    function updateToggleButtons() {
        // Получаем все элементы с классом "toggle"
        const toggles = document.querySelectorAll('.toggle');

        toggles.forEach(toggle => {
            // Находим элемент с классом "toggle-symbol" внутри текущего toggle
            const symbol = toggle.querySelector('.toggle-symbol');
            const button = toggle.querySelector('button');

            if (symbol && button) {
                // Проверяем значение символа и изменяем текст кнопки
                if (symbol.textContent.trim() === '+') {
                    button.textContent = 'Раскрыть всю группу';
                    toggle.style.cursor = "pointer";
                    button.style.backgroundColor = "#e8deff";
                } else if (symbol.textContent.trim() === '-') {
                    button.textContent = 'Скрыть всю группу';
                    toggle.style.cursor = "pointer";
                    button.style.backgroundColor = "#fbfad2";
                }
            }
        });
    }

    addExpandButton();

    const observer = new MutationObserver(updateToggleButtons);
    observer.observe(document.body, { childList: true, subtree: true });

    const scrollToTopButton = document.getElementById('scrollToTop');

    // Показываем/скрываем кнопку при прокрутке
    window.onscroll = function() {
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            scrollToTopButton.style.display = "block";
        } else {
            scrollToTopButton.style.display = "none";
        }
    };

    // Прокрутка вверх
    scrollToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth' // Плавная прокрутка
        });
    });

</script>

<?php

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

function displayTree(array $tree, $searchText = '') {
    echo '<ul>';
    foreach ($tree as $node) {
        if ($searchText == '') {
            echo '<li>';
            echo '<span class="toggle" onclick="toggle(this)">';
            if (!empty($node['children'])) {
                echo '<span class="toggle-symbol">+</span>'; // Символ +
            } else {
                echo '<span class="toggle-without-symbol"></span>'; // отступ, если нет детей
            }
            $text1 = htmlspecialchars($node['id']) . ' - ' . '<i>' . htmlspecialchars($node['text']) . '</i>';
            $text2 = htmlspecialchars($node['id']); // . ' - ' . htmlspecialchars($node['text']);
            echo $text1 . "<span class='copy-button' onclick=\"copyToClipboard('{$text2}'); event.stopPropagation();\">копировать</span>
            ";
            echo '</span>';
            if (!empty($node['children'])) {
                echo '<ul style="display:none;">'; 
                displayTree($node['children']); // Рекурсивно отображаем детей
                echo '</ul>';
            }
            echo '</li>';
        } 
    }
    echo '</ul>';
}

?>

</body>
</html>
