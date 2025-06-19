-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 19 2025 г., 14:01
-- Версия сервера: 5.6.51-log
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `tortotoro`
--

-- --------------------------------------------------------

--
-- Структура таблицы `assignment`
--

CREATE TABLE `assignment` (
  `id_assignment` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `assignment`
--


-- --------------------------------------------------------

--
-- Структура таблицы `dish`
--

CREATE TABLE `dish` (
  `id_dish` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `dish`
--

INSERT INTO `dish` (`id_dish`, `name`, `price`) VALUES
(1, 'Торт Наполеон', 300),
(2, 'Кофе', 50),
(3, 'Чизкейк', 150),
(4, 'Чай', 30),
(5, 'Японский кекс', 140),
(6, 'Мусс фисташка-вишня', 190),
(7, 'Моти', 70),
(8, 'Десерт таёжный', 370),
(9, 'Пирожное крем-брюле', 290),
(10, 'Пончики с малиновым вареньем', 470),
(11, 'Фисташковое мороженое', 240),
(12, 'Пломбир домашний', 230);

-- --------------------------------------------------------

--
-- Структура таблицы `item`
--

CREATE TABLE `item` (
  `id_item` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `item`
--

-- --------------------------------------------------------

--
-- Структура таблицы `order`
--

CREATE TABLE `order` (
  `id_order` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `shift_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `order`
--

-- --------------------------------------------------------

--
-- Структура таблицы `shift`
--

CREATE TABLE `shift` (
  `id_shift` int(11) NOT NULL,
  `time_start` datetime NOT NULL,
  `time_end` datetime NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `shift`
--


-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `photo_file` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id_user`, `login`, `password`, `name`, `photo_file`, `role_id`) VALUES
(1, 'cheban', '$2y$10$B.Rm3tVWbr0OS4/nJMyjQeNvqfHCWM5xVEcZmuAQ234C.omkIUwx2', 'Чебан Олег Олегович', NULL, 1),
(3, 'shakilonil', '$2y$10$Y6MP260n3RUVAjiRRCFG/eQaLDAqSnFighfnHjSl/1zViH0GnbSiW', 'Шакирова Валерия Александровна', NULL, 2),
(4, 'olga', '$2y$10$N/VSYXSvtL7e3IE7OMXMP.Y9rdtf13YfgIJC39pj0UEy4wGQmmSja', 'Лыскова Ольга Анатольевна', NULL, 3);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`id_assignment`),
  ADD KEY `shift_id` (`shift_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `dish`
--
ALTER TABLE `dish`
  ADD PRIMARY KEY (`id_dish`);

--
-- Индексы таблицы `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Индексы таблицы `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `shift_id` (`shift_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `shift`
--
ALTER TABLE `shift`
  ADD PRIMARY KEY (`id_shift`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `assignment`
--
ALTER TABLE `assignment`
  MODIFY `id_assignment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT для таблицы `dish`
--
ALTER TABLE `dish`
  MODIFY `id_dish` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `item`
--
ALTER TABLE `item`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `order`
--
ALTER TABLE `order`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `shift`
--
ALTER TABLE `shift`
  MODIFY `id_shift` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `assignment_ibfk_1` FOREIGN KEY (`shift_id`) REFERENCES `shift` (`id_shift`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id_order`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dish` (`id_dish`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`shift_id`) REFERENCES `shift` (`id_shift`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
