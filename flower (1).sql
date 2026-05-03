-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Апр 28 2026 г., 13:46
-- Версия сервера: 8.0.30
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `flower`
--

-- --------------------------------------------------------

--
-- Структура таблицы `assorti`
--

CREATE TABLE `assorti` (
  `id` int NOT NULL,
  `category` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `kolvo` int NOT NULL,
  `cena` int NOT NULL,
  `skidka` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `assorti`
--

INSERT INTO `assorti` (`id`, `category`, `name`, `foto`, `kolvo`, `cena`, `skidka`) VALUES
(1, 'Цветы', 'Амариллис', 'flow1.png', 20, 1000, 1),
(2, 'Цветы', 'Бувардия', 'flow2.jpg', 10, 1500, 0),
(3, 'Цветы', 'Вибурнум', 'flow3.jpg', 5, 3000, 0),
(4, 'Цветы', 'Агапантус', 'flow4.jpg', 15, 1000, 0),
(5, 'Цветы', 'Аллиум', 'flow5.jpg', 20, 1800, 0),
(6, 'Цветы', 'Астра', 'flow6.jpg', 35, 800, 0),
(7, 'Цветы', 'Ирис', 'flow7.jpg', 25, 1200, 0),
(8, 'Кашпо', 'Керамические', 'keramica.png', 36, 324, 0),
(9, 'Кашпо', 'Пластиковые', 'plastic.png', 42, 825, 0),
(10, 'Декор', 'Лента', 'ribbon.png', 7, 200, 0),
(11, 'Декор', 'Бумага', 'bumaga.png', 23, 250, 0),
(12, 'Декор', 'Корзина', 'basket.png', 36, 500, 0),
(13, 'Кашпо', 'Подвесные', 'up.png', 8, 825, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `klient`
--

CREATE TABLE `klient` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `e-mail` varchar(100) NOT NULL,
  `phone` varchar(16) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `klient`
--

INSERT INTO `klient` (`id`, `name`, `e-mail`, `phone`, `password`) VALUES
(1, 'Иванов Алексей Дмитриевич', 'a.ivanov@gmail.com', '2515515561', 'qwer1234'),
(2, 'Смирнов Михаил Андреевич', 'm.smirnov@gmail.com', '4373475475', 'adfdfbva4rte434'),
(3, 'Петров Дмитрий Сергеевич', 'm.smirnov@gmail.com', '3434367678', 'dfbgndxn5y'),
(4, 'Кузнецов Артём Викторович', 'a.kuznetsov@gmail.com', '435346657', 'ghjdgmdgjtytdu6665444'),
(5, 'Соколов Илья Романович', 'i.sokolov@gmail.com', '356262343', 'rgrrh565ujyj');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(16) NOT NULL,
  `e-mail` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `e-mail`, `password`, `status`) VALUES
(1, 'Беляева Виктория Михайловна', '9518225347', 'selendhard@gmail.com', 'asdf12zx67nm', 'админ'),
(2, 'Новикова Мария Игоревна', '7483483324', 'newmar@gmail.com', 'qwer1234', 'сотрудник');

-- --------------------------------------------------------

--
-- Структура таблицы `zakaz`
--

CREATE TABLE `zakaz` (
  `id` int NOT NULL,
  `id_klient` int NOT NULL,
  `id_users` int NOT NULL,
  `spos_opl` varchar(20) NOT NULL,
  `spos_dos` varchar(20) NOT NULL,
  `add_dos` varchar(200) NOT NULL,
  `opl_dos` int NOT NULL,
  `summa` int NOT NULL,
  `status` varchar(30) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `zakaz`
--

INSERT INTO `zakaz` (`id`, `id_klient`, `id_users`, `spos_opl`, `spos_dos`, `add_dos`, `opl_dos`, `summa`, `status`, `date`) VALUES
(1, 1, 1, 'Наличные', 'Доставка', 'Ул.Гороха Д. 35 Кв. 107 ', 0, 5972, 'доставлен', '2026-04-20 10:30:00'),
(2, 2, 1, 'Наличные', 'Самовывоз', '-', 0, 6100, 'в обработке', '2026-04-21 14:15:00'),
(3, 3, 2, 'Безналичные', 'Самовывоз', '-', 0, 5650, 'новый', '2026-04-23 09:45:00');

-- --------------------------------------------------------

--
-- Структура таблицы `zakaz_items`
--

CREATE TABLE `zakaz_items` (
  `id` int NOT NULL,
  `id_zakaz` int NOT NULL,
  `id_assorti` int NOT NULL,
  `quantity` int NOT NULL,
  `price_at_moment` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `zakaz_items`
--

INSERT INTO `zakaz_items` (`id`, `id_zakaz`, `id_assorti`, `quantity`, `price_at_moment`) VALUES
(1, 1, 1, 2, 1000),
(2, 1, 3, 1, 3000),
(3, 1, 8, 3, 324),
(4, 2, 2, 1, 1500),
(5, 2, 5, 2, 1800),
(6, 2, 10, 5, 200),
(7, 3, 4, 3, 1000),
(8, 3, 7, 2, 1200),
(9, 3, 11, 1, 250);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `assorti`
--
ALTER TABLE `assorti`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `klient`
--
ALTER TABLE `klient`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `zakaz`
--
ALTER TABLE `zakaz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_klient` (`id_klient`,`id_users`),
  ADD KEY `id_users` (`id_users`);

--
-- Индексы таблицы `zakaz_items`
--
ALTER TABLE `zakaz_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_zakaz` (`id_zakaz`),
  ADD KEY `id_assorti` (`id_assorti`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `assorti`
--
ALTER TABLE `assorti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `klient`
--
ALTER TABLE `klient`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `zakaz`
--
ALTER TABLE `zakaz`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `zakaz_items`
--
ALTER TABLE `zakaz_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `zakaz`
--
ALTER TABLE `zakaz`
  ADD CONSTRAINT `fk_zakaz_klient` FOREIGN KEY (`id_klient`) REFERENCES `klient` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_zakaz_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `zakaz_items`
--
ALTER TABLE `zakaz_items`
  ADD CONSTRAINT `fk_zakaz_items_assorti` FOREIGN KEY (`id_assorti`) REFERENCES `assorti` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_zakaz_items_zakaz` FOREIGN KEY (`id_zakaz`) REFERENCES `zakaz` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
