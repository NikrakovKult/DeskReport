<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
     #notes-block {
  display: grid;
  grid-template-columns: repeat(3, 1fr); /* 3 columns */
  grid-gap: 10px;
}

.note {
  background-color: #f0f0f0;
  padding: 10px;
  border: 1px solid #ccc;
  margin-bottom: 10px;
}
      #modal-create-note {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #fff;
        padding: 20px;
        border: 1px solid #ccc;
        display: none;
      }
    </style>
</head>
<body>
<button id="create-note-btn">Создать заметку</button>

<!-- Модальное окно для создания заметки -->
<div id="modal-create-note">
  <form id="create-note-form">
    <label for="title">Заголовок:</label>
    <input type="text" id="title" name="title"><br><br>
    <label for="text">Текст:</label>
    <textarea id="text" name="text"></textarea><br><br>
    <button id="save-note-btn">Сохранить</button>
  </form>
</div>

<!-- Блок для отображения заметок -->
<div id="notes-block"></div>

<script>
     fetch('load_notes.php')
   .then(response => response.json())
   .then(data => {
        var notesHtml = '';
        data.notes.forEach(function(note) {
            notesHtml += note;
        });
        document.getElementById('notes-block').innerHTML = notesHtml;
    });
    document.getElementById('create-note-btn').addEventListener('click', function() {
      document.getElementById('modal-create-note').style.display = 'block';
    });

    document.getElementById('save-note-btn').addEventListener('click', function(event) {
      event.preventDefault(); // добавляем это
      var formData = new FormData(document.getElementById('create-note-form'));
      fetch('create_note.php', {
        method: 'POST',
        body: formData
      })
    .then(response => response.json())
    .then(data => {
        document.getElementById('modal-create-note').style.display = 'none';
        document.getElementById('notes-block').innerHTML += data.note;
      });
    });
</script>
</body>
</html>