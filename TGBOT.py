import telebot
from telebot import types
import mysql.connector
import os
import uuid
import time
import threading

# Подключение к базе данных
conn = mysql.connector.connect(
    user='root',
    password='',
    host='localhost',
    database='DeskReport'
)
cursor = conn.cursor()

# Токен бота
TOKEN = "7019580590:AAEmPJy5iiWEEWFMH3YWFROVp6GFR6YwAR4"

bot = telebot.TeleBot(TOKEN)
user_data = {}

@bot.message_handler(commands=['start'])
@bot.message_handler(commands=['start'])
def start_command(message):
    user_data[message.chat.id] = {}
    user_data[message.chat.id]['orders'] = {}  # Add this line
    bot.send_message(message.chat.id, 'Привет, я бот сервиса технической поддержки DeskPlusReport')
    bot.send_message(message.chat.id, 'Для начала введите свой телефон, чтобы я нашёл вас в моей базе данных')
    bot.send_message(message.chat.id, 'Введите номер телефона:')
    bot.register_next_step_handler(message, auth_by_phone, user_data)
    markup = types.ReplyKeyboardMarkup(resize_keyboard=True)
    markup.add('Новая заявка')
    markup.add('Мои заявки')
    bot.send_message(message.chat.id, 'Выберите действие:', reply_markup=markup)

def auth_by_phone(message, user_data):
    phone = message.text
    cursor.execute("SELECT * FROM clients WHERE mobile = %s", (phone,))
    client = cursor.fetchone()
    if client:
        user_data[message.chat.id] = {}
        user_data[message.chat.id]['fio'] = client[1]
        user_data[message.chat.id]['email'] = client[2]
        user_data[message.chat.id]['mobile'] = client[3]
        user_data[message.chat.id]['otdel'] = client[4]
        user_data[message.chat.id]['doljnost'] = client[5]
        bot.send_message(message.chat.id, 'Данные найдены')
        markup = types.ReplyKeyboardMarkup(resize_keyboard=True)
        markup.add('Новая заявка')
        markup.add('Мои заявки')
        bot.send_message(message.chat.id, 'Выберите действие:', reply_markup=markup)
    else:
        bot.send_message(message.chat.id, 'Данные не найдены.')
        bot.send_message(message.chat.id, 'Тогда давайте вас добавим')
        bot.send_message(message.chat.id, 'Введите ваше ФИО Пример(Иванов Иван Иванович)')
        bot.register_next_step_handler(message, get_fio, user_data)

def get_fio(message, user_data):
    user_data[message.chat.id]['fio'] = message.text
    bot.send_message(message.chat.id, 'Введите email:')
    bot.register_next_step_handler(message, get_email, user_data)

def get_email(message, user_data):
    user_data[message.chat.id]['email'] = message.text
    bot.send_message(message.chat.id, 'Введите мобильный номер:')
    bot.register_next_step_handler(message, get_mobile, user_data)

def get_mobile(message, user_data):
    user_data[message.chat.id]['mobile'] = message.text
    bot.send_message(message.chat.id, 'Введите отдел:')
    bot.register_next_step_handler(message, get_otdel, user_data)

def get_otdel(message, user_data):
    user_data[message.chat.id]['otdel'] = message.text
    bot.send_message(message.chat.id, 'Введите должность:')
    bot.register_next_step_handler(message, get_doljnost, user_data)

def get_doljnost(message, user_data):
    user_data[message.chat.id]['doljnost'] = message.text
    cursor.execute("INSERT INTO clients (fio, email, mobile, otdel, doljnost) VALUES (%s, %s, %s, %s, %s)",
                    (user_data[message.chat.id]['fio'], user_data[message.chat.id]['email'], user_data[message.chat.id]['mobile'], user_data[message.chat.id]['otdel'], user_data[message.chat.id]['doljnost']))
    conn.commit()
    bot.send_message(message.chat.id, 'Вы добавлены!')
    markup = types.ReplyKeyboardMarkup(resize_keyboard=True)
    markup.add('Новая заявка')
    markup.add('Мои заявки')
    bot.send_message(message.chat.id, 'Выберите действие:', reply_markup=markup)

def my_requests(message):
    if message.chat.id in user_data and 'fio' in user_data[message.chat.id]:
        cursor.execute("SELECT * FROM orders WHERE Sender = %s AND Status!= 'Завершено'", (user_data[message.chat.id]['fio'],))
        orders = cursor.fetchall()
        if orders:
            text = 'Ваши заявки:\n'
            for order in orders:
                text += f"Заявка {order[0]} - {order[1]} - {order[5]} ({order[4]})\n"
            bot.send_message(message.chat.id, text)
        else:
            bot.send_message(message.chat.id, 'У вас нет активных заявок')
    else:
        bot.send_message(message.chat.id, 'Вы не авторизованы')

def new_request(message):
    if message.text == 'Новая заявка':
        bot.send_message(message.chat.id, 'Что у вас за проблема?')
        bot.register_next_step_handler(message, get_discrip)
    else:
        bot.send_message(message.chat.id, 'Неверная команда')
   
def get_discrip(message):
    user_data[message.chat.id]['discrip'] = message.text
    markup = types.InlineKeyboardMarkup()
    markup.add(types.InlineKeyboardButton('Пропустить', callback_data='skip_photo'))
    bot.send_message(message.chat.id, 'Загрузите фото (до 3):', reply_markup=markup)
    bot.register_next_step_handler(message, upload_photos)

def upload_photos(message):
    if message.content_type == 'photo':
        file_info = bot.get_file(message.photo[-1].file_id)
        downloaded_file = bot.download_file(file_info.file_path)
        filename = f"{message.photo[-1].file_id}.jpg"
        with open(os.path.join('G:\\OSPanel\\domains\\DeskPlusReport\\uploads', filename), 'wb') as new_file:
            new_file.write(downloaded_file)
        user_data[message.chat.id]['photos'].append(filename)
        create_request(message, user_data)
    else:
        bot.send_message(message.chat.id, 'Упс, кажется вы прислали не то что я просил... ')
        bot.send_message(message.chat.id, 'Пожалуйста, отправьте мне фото ')
        bot.register_next_step_handler(message, upload_photos)

@bot.callback_query_handler(func=lambda call: True)
def callback_inline(call):
    if call.data == 'skip_photo':
        user_data[call.message.chat.id]['photos'] = ['', '', '']
        create_request(call.message, user_data, skip_photos=True)

def create_request(message, user_data, skip_photos=False):
    if hasattr(message, 'chat') and 'id' in message.chat.__dict__:
        user_id = message.chat.id
        discrip = user_data[user_id]['discrip']
        fio = user_data[user_id]['fio']
        photos = user_data[user_id]['photos']
        query = "INSERT INTO orders (Discrip, Sender, Specialist, Date_by, Status, Photo1, Photo2, Photo3) VALUES (%s, %s, 'Не назначенно', NOW(), 'Новая', %s, %s, %s)"
        cursor.execute(query, (discrip, fio, photos[0], photos[1], photos[2]))
        conn.commit()
        order_id = cursor.lastrowid
       
        bot.send_message(user_id, f'Ваша заявка #{order_id} принята, ожидайте ответа специалиста')
        bot.send_message(user_id, 'Спасибо за обращение в техническую поддержку DeskPlusReport')
    else:
        print(f"Error: message object has no chat attribute or chat id: {message}")

def check_order_status(message, order_id):
    while True:
        time.sleep(10)
        cursor.execute("SELECT Status FROM orders WHERE id = %s", (order_id,))
        status = cursor.fetchone()[0]
        if status != user_data[message.chat.id]['orders'][order_id]['status']:
            user_data[message.chat.id]['orders'][order_id]['status'] = status
            send_order_status_update(message, order_id, status)

def send_order_status_update(message, order_id, new_status):
    bot.send_message(message.chat.id, f"Статус заявки {order_id} изменен на {new_status}")

@bot.message_handler(content_types=['text'])
def handle_text(message):
    if message.chat.id in user_data and 'fio' in user_data[message.chat.id]:
        if message.text == 'Новая заявка':
            new_request(message)
        elif message.text == 'Мои заявки':
            my_requests(message)
        else:
            markup = types.ReplyKeyboardMarkup(resize_keyboard=True)
            markup.add('Новая заявка')
            markup.add('Мои заявки')
            bot.send_message(message.chat.id, 'Выберите действие:', reply_markup=markup)
    else:
        bot.send_message(message.chat.id, 'Вы не авторизованы')

bot.polling(none_stop=True)
conn.close()