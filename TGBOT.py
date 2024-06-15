import telebot
from telebot import types
import mysql.connector
import os
import uuid
import time
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
def check_status(message):
    cursor.execute("SELECT * FROM orders WHERE Sender = %s", (user_data[message.chat.id]['fio'],))
    orders = cursor.fetchall()
    for order in orders:
        if order[5] != 'Новая':  # если статус заявки не новый
            bot.send_message(message.chat.id, f"Статус заявки {order[0]} изменен на {order[5]}")

    while True:
        for chat_id in user_data:
            check_status(chat_id)
        time.sleep(60)  # проверять статус каждую минуту
@bot.message_handler(commands=['start'])
def start_command(message):
    user_data[message.chat.id] = {}
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
        bot.send_message(message.chat.id, 'Вы авторизованы!')
        
        markup = types.ReplyKeyboardMarkup(resize_keyboard=True)
        markup.add('Новая заявка')
        markup.add('Мои заявки')
        bot.send_message(message.chat.id, 'Выберите действие:', reply_markup=markup)

def my_requests(message):
    if message.chat.id in user_data and 'fio' in user_data[message.chat.id]:
        cursor.execute("SELECT * FROM orders WHERE Sender = %s", (user_data[message.chat.id]['fio'],))
    orders = cursor.fetchall()
    if orders:
        text = 'Ваши заявки:\n'
        for order in orders:
            text += f"Заявка {order[0]} - {order[4]} ({order[5]}) - {order[1]}\n"
        bot.send_message(message.chat.id, text)
    else:
        bot.send_message(message.chat.id, 'У вас нет заявок')
                
def new_request(message):
    if message.text == 'Новая заявка':
        bot.send_message(message.chat.id, 'Введите описание проблемы:')
        bot.register_next_step_handler(message, get_discrip)
    else:
        bot.send_message(message.chat.id, 'Неверная команда')
@bot.message_handler(content_types=['text'])
def handle_text(message):
    if message.text == 'Новая заявка':
        new_request(message)
    elif message.text == 'Мои заявки':
        my_requests(message)
    else:
        bot.send_message(message.chat.id, 'Неверная команда')

@bot.message_handler(content_types=['text'])
def handle_text(message):
    if message.text == 'Новая заявка':
        new_request(message)

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
    bot.send_message(message.chat.id, 'Вы зарегистрированы!')
    bot.send_message(message.chat.id, 'Введите описание проблемы:')
    bot.register_next_step_handler(message, get_discrip)

def get_discrip(message):
    if message.chat.id not in user_data:
        user_data[message.chat.id] = {}
    user_data[message.chat.id]['discrip'] = message.text
    bot.send_message(message.chat.id, 'Загрузите фото:')
    bot.register_next_step_handler(message, upload_photo)

def upload_photo(message):
    if message.content_type == 'photo':
        photo = message.photo[-1].file_id
        file_info = bot.get_file(photo)
        downloaded_file = bot.download_file(file_info.file_path)
        filename = f"image.psd ({uuid.uuid4()}).png"
        with open(os.path.join('G:\\OSPanel\\domains\\DeskPlusReport\\uploads', filename), 'wb') as new_file:
            new_file.write(downloaded_file)
        user_data[message.chat.id]['photos'] = [filename]
        bot.send_message(message.chat.id, 'Загрузите еще фото (до 3):')
        bot.register_next_step_handler(message, upload_more_photos)
    else:
        user_data[message.chat.id]['photos'] = []
        markup = types.InlineKeyboardMarkup()
        markup.add(types.InlineKeyboardButton('Пропустить', callback_data='skip_photo'))
        bot.send_message(message.chat.id, 'Загрузите фото или пропустите:', reply_markup=markup)
        return

def upload_more_photos(message):
    if message.content_type == 'photo':
        photo = message.photo[-1].file_id
        file_info = bot.get_file(photo)
        downloaded_file = bot.download_file(file_info.file_path)
        with open(os.path.join('G:\\OSPanel\\domains\\DeskPlusReport\\uploads', f"{photo}.jpg"), 'wb') as new_file:
            new_file.write(downloaded_file)
        user_data[message.chat.id]['photos'].append(f"{photo}.jpg")
        if len(user_data[message.chat.id]['photos']) < 3:
            bot.send_message(message.chat.id, 'Загрузите еще фото (до 3):')
            bot.register_next_step_handler(message, upload_more_photos)
        else:
            process_photos(message)
    else:
        process_photos(message)
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

        photos = user_data[message.chat.id]['photos']
        photo1 = f"uploads/{photos[0]}" if photos else ''
        photo2 = f"uploads/{photos[1]}" if len(photos) > 1 else ''
        photo3 = f"uploads/{photos[2]}" if len(photos) > 2 else ''

        cursor.execute("INSERT INTO orders (Discrip, Sender, Specialist, Date_by, Status, Photo1, Photo2, Photo3) VALUES (%s, %s, 'Не назначенно', NOW(), 'Новая', %s, %s, %s)",
                        (user_data[message.chat.id]['discrip'], user_data[message.chat.id]['fio'], photo1, photo2, photo3))
        conn.commit()
        bot.send_message(message.chat.id, 'Заявка отправлена!')
    else:
        bot.send_message(message.chat.id, 'Ошибка: пользователь не авторизован')

bot.polling()