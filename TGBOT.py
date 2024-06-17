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
def start_command(message):
    user_data[message.chat.id] = {}
    user_data[message.chat.id]['orders'] = {}
    bot.send_message(message.chat.id, 'Привет, я бот сервиса технической поддержки DeskPlusReport')
    bot.send_message(message.chat.id, 'Для начала введите свой телефон, чтобы я нашёл вас в моей базе данных')
    bot.send_message(message.chat.id, 'Введите номер телефона:')
    bot.register_next_step_handler(message, auth_by_phone)

def auth_by_phone(message):
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
        bot.register_next_step_handler(message, get_fio)

def my_requests(message):
    if message.chat.id in user_data and 'fio' in user_data[message.chat.id]:
        cursor.execute("SELECT * FROM orders WHERE Sender = %s AND Status!= 'Завершено'", (user_data[message.chat.id]['fio'],))
        orders = cursor.fetchall()
        if orders:
            text = 'Ваши заявки:\n'
            for order in orders:
                text += f"Заявка {order[0]} - {order[4]} ({order[5]}) - {order[1]}\n"
            bot.send_message(message.chat.id, text)
        else:
            bot.send_message(message.chat.id, 'У вас нет заявок')
    else:
        bot.send_message(message.chat.id, 'Вы не авторизованы')
    return  # остановка выполнения функции
                
def new_request(message):
    if message.text == 'Новая заявка':
        bot.send_message(message.chat.id, 'Что у вас за проблема?')
        bot.register_next_step_handler(message, get_discrip)
    else:
        bot.send_message(message.chat.id, 'Неверная команда')

@bot.message_handler(content_types=['text'])
def handle_text(message):
    if message.text == 'Новая заявка':
        bot.send_message(message.chat.id, 'Что у вас за проблема?')
        bot.register_next_step_handler(message, get_discrip)
    elif message.text == 'Мои заявки':
        if 'fio' in user_data[message.chat.id]:
            my_requests(message)
        else:
            bot.send_message(message.chat.id, 'Вы не авторизованы')
    else:
        bot.send_message(message.chat.id, 'Неверная команда')
        
def get_fio(message):
    user_data[message.chat.id] = {}
    user_data[message.chat.id]['fio'] = message.text
    bot.send_message(message.chat.id, 'Введите email:')
    bot.register_next_step_handler(message, get_email)

def get_email(message):
    user_data[message.chat.id]['email'] = message.text
    bot.send_message(message.chat.id, 'Введите мобильный номер:')
    bot.register_next_step_handler(message, get_mobile)

def get_mobile(message):
    user_data[message.chat.id]['mobile'] = message.text
    bot.send_message(message.chat.id, 'Введите отдел:')
    bot.register_next_step_handler(message, get_otdel)

def get_otdel(message):
    user_data[message.chat.id]['otdel'] = message.text
    bot.send_message(message.chat.id, 'Введите должность:')
    bot.register_next_step_handler(message, get_doljnost)

def get_doljnost(message):
    user_data[message.chat.id]['doljnost'] = message.text
    cursor.execute("INSERT INTO clients (fio, email, mobile, otdel, doljnost) VALUES (%s, %s, %s, %s, %s)",
                    (user_data[message.chat.id]['fio'], user_data[message.chat.id]['email'], user_data[message.chat.id]['mobile'], user_data[message.chat.id]['otdel'], user_data[message.chat.id]['doljnost']))
    conn.commit()
    bot.send_message(message.chat.id, 'Вы добавлены!')
    markup = types.ReplyKeyboardMarkup(resize_keyboard=True)
    markup.add('Новая заявка')
    markup.add('Мои заявки')
    bot.send_message(message.chat.id, 'Выберите действие:', reply_markup=markup)

def get_discrip(message):
    if message.chat.id not in user_data:
        user_data[message.chat.id] = {}
    user_data[message.chat.id]['discrip'] = message.text
    markup = types.InlineKeyboardMarkup()
    markup.add(types.InlineKeyboardButton('Пропустить', callback_data='skip_photo'))
    bot.send_message(message.chat.id, 'Загрузите фото (до 3):', reply_markup=markup)
    if new_request:
        bot.register_next_step_handler(message, upload_photos)

def upload_photos(message):
    if message.content_type == 'photo':
        photos = message.photo
        user_data[message.chat.id]['photos'] = []
        for photo in photos:
            file_info = bot.get_file(photo.file_id)
            downloaded_file = bot.download_file(file_info.file_path)
            filename = f"{photo.file_id}.jpg"
            with open(os.path.join('G:\\OSPanel\\domains\\DeskPlusReport\\uploads', filename), 'wb') as new_file:
                new_file.write(downloaded_file)
            user_data[message.chat.id]['photos'].append(filename)
        process_photos(message)
    else:
        bot.send_message(message.chat.id, 'Упс, кажется вы прислали не то что я просил... ')
        bot.send_message(message.chat.id, 'Пожалуйста, отправьте мне фото ')
        bot.register_next_step_handler(message, upload_photos)  
        
def process_photos(message):
    if 'fio' in user_data[message.chat.id] and 'email' in user_data[message.chat.id] and 'mobile' in user_data[message.chat.id] and 'otdel' in user_data[message.chat.id] and 'doljnost' in user_data[message.chat.id]:
        cursor.execute("SELECT * FROM clients WHERE fio = %s AND email = %s AND mobile = %s AND otdel = %s AND doljnost = %s",
                        (user_data[message.chat.id]['fio'], user_data[message.chat.id]['email'], user_data[message.chat.id]['mobile'], user_data[message.chat.id]['otdel'], user_data[message.chat.id]['doljnost']))
        client = cursor.fetchone()
        if client:
            client_id = client[0]
        else:
            cursor.execute("INSERT INTO clients (fio, email, mobile, otdel, doljnost) VALUES (%s, %s, %s, %s, %s)",
                            (user_data[message.chat.id]['fio'], user_data[message.chat.id]['email'], user_data[message.chat.id]['mobile'], user_data[message.chat.id]['otdel'], user_data[message.chat.id]['doljnost']))
            client_id = cursor.lastrowid

        if 'photos' in user_data[message.chat.id]:
            photos = user_data[message.chat.id]['photos']
            photo1 = f"uploads/{photos[0]}" if photos else ''
            photo2 = f"uploads/{photos[1]}" if len(photos) > 1 else ''
            photo3 = f"uploads/{photos[2]}" if len(photos) > 2 else ''
        else:
            photo1 = photo2 = photo3 = ''

        cursor.execute("INSERT INTO orders (Discrip, Sender, Specialist, Date_by, Status, Photo1, Photo2, Photo3) VALUES (%s, %s, 'Не назначенно', NOW(), 'Новая', %s, %s, %s)",
                        (user_data[message.chat.id]['discrip'], user_data[message.chat.id]['fio'], photo1, photo2, photo3))
        conn.commit()
        bot.send_message(message.chat.id, 'Заявка отправлена!')
        global new_request
        
    else:
        bot.send_message(message.chat.id, 'Ошибка при отправке заявки')

def check_order_status(message):
    if message.chat.id in user_data and 'fio' in user_data[message.chat.id]:
        while True:
            cursor.execute("SELECT * FROM orders WHERE Sender = %s", (user_data[message.chat.id]['fio'],))
            orders = cursor.fetchall()
            for order in orders:
                if order[0] not in user_data[message.chat.id]['orders'] or user_data[message.chat.id]['orders'][order[0]]['status'] != order[4]:
                    if order[0] not in user_data[message.chat.id]['orders']:
                        user_data[message.chat.id]['orders'][order[0]] = {}
                    user_data[message.chat.id]['orders'][order[0]]['status'] = order[4]
                    if order[4] == 'Завершено':
                        del user_data[message.chat.id]['orders'][order[0]]
                        bot.send_message(message.chat.id, f'Заявка {order[0]} - {order[4]} ({order[5]}) - {order[1]} завершена')
            time.sleep(10)  # wait for 10 seconds before checking again
    else:
        time.sleep(10)  # wait for 10 seconds before checking again
        
def send_order_status_update(message, order_id, new_status):
    if order_id in user_data[message.chat.id]['orders'] and user_data[message.chat.id]['orders'][order_id]['status'] != new_status:
        bot.send_message(message.chat.id, f'Статус заявки {order_id} изменен на {new_status}')
        user_data[message.chat.id]['orders'][order_id]['status'] = new_status
        time.sleep(10)

@bot.callback_query_handler(func=lambda call: True)
def callback_inline(call):
    if call.data == 'skip_photo':
        process_photos(call.message)



bot.polling(none_stop=True)