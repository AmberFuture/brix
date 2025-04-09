<?php
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_TITLE"] = "Информация о работе модуля";
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_1"] = "Показ/скрытие каких полей можно настроить?";
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_1"] = '<p class="ui-slider-paragraph-2">Настроить показ и скрытие полей можно всех типов <a class="ui-slider-link" href="/bitrix/admin/userfield_admin.php" target="_blank">пользовательских полей</a>.</p>';
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_2"] = "Можно ли изменить настраиваемое поле?";
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_2"] = '
<p class="ui-slider-paragraph-2">Настраиваемое поле можно изменять только на этапе добавления нового правила. После сохранения правила изменить поле нельзя.</p>
<img class="brix-info__img" src="images/question_2.jpg?'. strtotime(date("d-m-Y")) . '" alt="Настраиваемое поле">
';
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_3"] = "Сколько правил можно создавать для одного поля?";
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_3"] = '
<p class="ui-slider-paragraph-2">Для одного поля можно создать лишь одно правило, но внутри правила доступно создание неограниченного количества условий.</p>
<img class="brix-info__img" src="images/question_3.jpg?'. strtotime(date("d-m-Y")) . '" alt="Условия внутри правила">
';
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_4"] = "Можно ли отключить работу правила, не удаляя его?";
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_4"] = '
<p class="ui-slider-paragraph-2">Да, для этого достаточно снять активность с правила.</p>
<img class="brix-info__img" src="images/question_4.jpg?'. strtotime(date("d-m-Y")) . '" alt="Отключение правила">
';
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_5"] = 'Для чего нужна настройка "обязательное поле" и чем она отличается от аналогичной настройки пользовательского поля?';
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_5"] = '
<p class="ui-slider-paragraph-2">В отличие от дефолтной настройки обязательности у пользовательского поля данная настройка в правиле позволяет задать обязательность заполнения поля только при выполнении настроенных условий. Если условия выполняются и поле задано как обязательное, но пользователь его не заполнил, то при сохранении задачи будет выведена ошибка.</p>
<img class="brix-info__img" src="images/question_5.jpg?'. strtotime(date("d-m-Y")) . '" alt="Обязательное поле">
';
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_6"] = "Для каких полей можно настроить условия?";
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_6"] = '
<p class="ui-slider-paragraph-2">Настроить условия можно для ряда дефолтных полей:</p>
<ol class="ui-slider-list">
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Исполнитель&nbsp;/ Постановщик</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Крайний срок</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Проект</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">CRM</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Теги</span>
    </li>
</ol><br>
<p class="ui-slider-paragraph-2">А также для следующих типов пользовательских полей:</p>
<ol class="ui-slider-list">
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Да/Нет</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Дата и дата со временем</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Привязка к разделам&nbsp;/ элементам инф. блоков</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Привязка к сотруднику</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Привязка к элементам CRM</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Список</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Строка</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text">Число&nbsp;/ Целое число</span>
    </li>
</ol><br>
<img class="brix-info__img" src="images/question_6.jpg?'. strtotime(date("d-m-Y")) . '" alt="Поля для условий">
';
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_7"] = "От чего зависит список типов условий? Что означает каждый тип условия?";
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_7"] = '
<p class="ui-slider-paragraph-2">Список доступных типов условий зависит от типа поля и настройки множественности. Ниже представлена таблица соотнесения условий и полей, а также описание условий.</p>
<table class="brix-info__table">
    <tbody>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text">Тип поля</th>
            <th class="brix-info__th ui-slider-list-text">Множест&shy;венное</th>
            <th class="brix-info__th ui-slider-list-text">Заполнено</th>
            <th class="brix-info__th ui-slider-list-text">(Не) содержится в</th>
            <th class="brix-info__th ui-slider-list-text">(Не) содержит</th>
            <th class="brix-info__th ui-slider-list-text">(Не) равно</th>
            <th class="brix-info__th ui-slider-list-text">Больше текущей даты <br>на&nbsp;X&nbsp;дней</th>
            <th class="brix-info__th ui-slider-list-text">
                Меньше (или равно)&nbsp;/ <br>
                Больше (или равно)&nbsp;/ <br>
                Диапазон
            </th>
        </tr>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text">Исполнитель&nbsp;/ Постановщик</th>
            <td class="brix-info__td">Нет</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text">Крайний срок</th>
            <td class="brix-info__td">Нет</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text">Проект</th>
            <td class="brix-info__td">Нет</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text">CRM</th>
            <td class="brix-info__td">Да</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text">Теги</th>
            <td class="brix-info__td">Да</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text">Да/Нет</th>
            <td class="brix-info__td">Нет</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text" rowspan="2">Дата&nbsp;/ Дата со временем</th>
            <td class="brix-info__td">Нет</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <td class="brix-info__td">Да</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text" rowspan="2">
                Привязка к разделам <br>и&nbsp;элементам инф. блоков&nbsp;/ <br>
                Привязка к сотруднику&nbsp;/ <br>
                Привязка к элементам CRM&nbsp;/ <br>
                Список&nbsp;/ Строка
            </th>
            <td class="brix-info__td">Нет</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <td class="brix-info__td">Да</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
        </tr>
        <tr class="brix-info__tr">
            <th class="brix-info__th ui-slider-list-text" rowspan="2">Число&nbsp;/ Целое число</th>
            <td class="brix-info__td">Нет</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
        </tr>
        <tr class="brix-info__tr">
            <td class="brix-info__td">Да</td>
            <td class="brix-info__td">+</td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td"></td>
            <td class="brix-info__td">+</td>
        </tr>
    </tbody>
</table><br>
<ol class="ui-slider-list">
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Заполнено</b> &#8212; проверяет заполненность поля.</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Содержится в</b> &#8212; проверяется присутствие выбранного значения в заданных в условии значениях. Для поля с типом "Строка" проверяется, что введённая пользователем строка содержится в условии (регистр не имеет значения).</span>
        <span class="ui-slider-list-text">Пример со списочным полем "Статус согласования": если в условии выбраны "Согласовано" и "Утверждено", то условие выполнится, если пользователь выберет один из указанных вариантов.</span><br>
        <img class="brix-info__img" src="images/question_7_1.jpg?'. strtotime(date("d-m-Y")) . '" alt="Пример для условия содержится в">
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Не содержится в</b> &#8212; проверяется отсутствие выбранного значения в заданных в условии значениях. Для поля с типом "Строка" проверяется, что введённая пользователем строка не содержится в условии (регистр не имеет значения).</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Содержит</b> &#8212; проверяется присутствие всех заданных в условии значений в списке выбранных значений. Для поля с типом "Строка" проверяется, что любая из введённых пользователем строк содержит строку из условия (регистр не имеет значения).</span>
        <span class="ui-slider-list-text">Доступно указание нескольких вариантов проверки, между которыми проверка осуществляется с условием "ИЛИ".</span>
        <span class="ui-slider-list-text">Пример с полем "Теги": если указано два варианта условий &#8211; "CRM [ЗАО "МПЗК"], Аналитика [ЗАО "МПЗК"]" (в квадратных скобках указана привязка тега к проекту) и "сайт, аналитика", то должно выполниться одно из условий &#8211; пользователь выбрал CRM [ЗАО "МПЗК"] и Аналитика [ЗАО "МПЗК"] <b>ИЛИ</b> сайт и аналитика.</span><br>
        <img class="brix-info__img" src="images/question_7_2.jpg?'. strtotime(date("d-m-Y")) . '" alt="Пример для условия содержит">
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Не содержит</b> &#8212; проверяется отсутствие всех заданных в условии значений в списке выбранных значений. Для поля с типом "Строка" проверяется, что все введённые пользователем значения в строках не содержат строку из условия (регистр не имеет значения).</span>
        <span class="ui-slider-list-text">Доступно указание нескольких вариантов проверки, между которыми проверка осуществляется с условием "ИЛИ" для всех типов полей, кроме "Строка". Для строковых полей между подусловиями проверка осуществляется по условию "И".</span>
        <span class="ui-slider-list-text">Пример со строковым полем "Заказчик на стороне клиента": если указано два варианта условий &#8211; "Левкин" и "Аксин", то должны выполниться условия, что все введённые пользователем значения не содержат строку "Левкин" и не содержат строку "Аксин".</span>
        <img class="brix-info__img" src="images/question_7_3.jpg?'. strtotime(date("d-m-Y")) . '" alt="Пример для условия не содержит">
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Равно</b> &#8212; проверяется равенство одного из заданных вариантов в условии и выбранных пользователем значений. Для поля с типом "Строка" проверяется, что введённая пользователем строка равна строке в условии (регистр не имеет значения).</span>
        <span class="ui-slider-list-text">Доступно указание нескольких вариантов проверки, между которыми проверка осуществляется с условием "ИЛИ". Исключением является тип поля "Да/Нет", для которого доступно указание только одного возможного варианта.</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Не равно</b> &#8212; проверяется неравенство всех заданных вариантов в условии и выбранных пользователем значений. Для поля с типом "Строка" проверяется, что введённая пользователем строка не равна строке в условии (регистр не имеет значения).</span>
        <span class="ui-slider-list-text">Доступно указание нескольких вариантов проверки, между которыми проверка осуществляется с условием "И". Исключением является тип поля "Да/Нет", для которого доступно указание только одного возможного варианта.</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Больше текущей даты на X дней</b> &#8212; проверяется больше ли указанная пользователем дата текущей даты + X дней.</span>
        <span class="ui-slider-list-text">Пример: если текущая дата 20.10.2020, X дней равно 5, а пользователь указал 26.10.2020, то условие выполнится. При аналогичных условиях, но с указанием пользователем даты 25.10.2020, условие не выполнится, т.к. даты будут равны.</span>
        <span class="ui-slider-list-text">Если поле является множественным, то условие должно выполниться для одного из указанных пользователем значения.</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Меньше</b> &#8212; проверяется, что введённое пользователем число меньше числа из условия.</span>
        <span class="ui-slider-list-text">Если поле является множественным, то условие должно выполниться для одного из указанных пользователем значения.</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Меньше или равно</b> &#8212; проверяется, что введённое пользователем число меньше или равно числу из условия.</span>
        <span class="ui-slider-list-text">Если поле является множественным, то условие должно выполниться для одного из указанных пользователем значения.</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Больше</b> &#8212; проверяется, что введённое пользователем число больше числа из условия.</span>
        <span class="ui-slider-list-text">Если поле является множественным, то условие должно выполниться для одного из указанных пользователем значения.</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Больше или равно</b> &#8212; проверяется, что введённое пользователем число больше или равно числу из условия.</span>
        <span class="ui-slider-list-text">Если поле является множественным, то условие должно выполниться для одного из указанных пользователем значения.</span>
    </li>
    <li class="ui-slider-list-item">
        <span class="ui-slider-list-text"><b>Диапазон</b> &#8212; проверяется, что введённое пользователем число находится в диапазоне из условия: больше или равно первому число и меньше или равно второму числу.</span>
        <span class="ui-slider-list-text">Если в условии указать только одно число, то условие будет или "больше или равно" (при заполнении первого числа), или "меньше или равно" (при заполнении второго числа).</span>
        <span class="ui-slider-list-text">Если поле является множественным, то условие должно выполниться для одного из указанных пользователем значения.</span>
    </li>
</ol><br>
<img class="brix-info__img" src="images/question_7_4.jpg?'. strtotime(date("d-m-Y")) . '" alt="Типы условий">
';
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_8"] = "Какова логика работы промежуточных условий и/или?";
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_8"] = '
<p class="ui-slider-paragraph-2">Промежуточные условия и/или позволяют объединять и разделять несколько условий. Первым приоритетом является условие "И".</p>
<p class="ui-slider-paragraph-2">Рассмотрим следующий пример условий для показа поля "Бюджет (план)":</p>
<ul>
    <li class="brix-info__item">Для поля "Проект" задано условие "содержится в" = "Корп. сайт" или "Корп. портал" или "Офисная тех.поддержка"</li>
    <li class="brix-info__item">Промежуточное условие "ИЛИ"</li>
    <li class="brix-info__item">Для поля "Теги" задано условие "содержит" "CRM [ЗАО "МПЗК"]" или "Аналитика [ЗАО "МПЗК"]"</li>
    <li class="brix-info__item">Промежуточное условие "И"</li>
    <li class="brix-info__item">Для поля "Проект" задано условие "содержится в" = "ЗАО МПЗК"</li>
    <li class="brix-info__item">Промежуточное условие "ИЛИ"</li>
    <li class="brix-info__item">Для поля "Постановщик" задано условие "содержится в" = "Иван Иванов", "Департамент проектов" (включая дочерние подразделения) или "Отдел продаж (только сотрудники отдела)"</li>
    <li class="brix-info__item">Промежуточное условие "И"</li>
    <li class="brix-info__item">Для поля "Проект" задано условие "содержится в" = "Корп. портал"</li>
</ul>
<p class="ui-slider-paragraph-2">С перечисленными условиями поле "Бюджет (план)" будет отображаться, если выполнится один из блоков условий:</p>
<ul>
    <li class="brix-info__item">Выбран проект "Корп. сайт", "Корп. портал" или "Офисная тех.поддержка"</li>
    <li class="brix-info__item">В выбранных тегах есть "CRM [ЗАО "МПЗК"]" или "Аналитика [ЗАО "МПЗК"]" и выбран проект "ЗАО МПЗК"</li>
    <li class="brix-info__item">Постановщиком является Иван Иванов или сотрудники Департамента проектов (включая дочерние подразделения) или сотрудники Отдела продаж и выбран проект "Корп. портал"</li>
</ul><br>
<img class="brix-info__img" src="images/question_8.jpg?'. strtotime(date("d-m-Y")) . '" alt="Пример условий">
';
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_9"] = "Видеодемонстрация работы модуля";
$MESS["BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_9"] = '
<video class="brix-info__video" controls>
    <source src="video/demo.mp4" type="video/mp4"></source>
</video>
';