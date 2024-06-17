<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <script>
    import React, { useState } from 'eact';
import axios from 'axios';

function TaskCreator() {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [assignedTo, setAssignedTo] = useState('');
  const [users, setUsers] = useState([]);
  const [showModal, setShowModal] = useState(false);

  const handleCreateTask = async () => {
    try {
      const response = await axios.post('/tasks', {
        title,
        description,
        assignedTo,
      });
      console.log(response);
    } catch (error) {
      console.error(error);
    }
  };

  const handleGetUsers = async () => {
    try {
      const response = await axios.get('/users');
      setUsers(response.data);
    } catch (error) {
      console.error(error);
    }
  };

  return (
    <div>
      <button onClick={() => setShowModal(true)}>Создать задачу</button>
      {showModal && (
        <div className="modal">
          <h2>Создать задачу</h2>
          <form>
            <label>
              Название задачи:
              <input type="text" value={title} onChange={(e) => setTitle(e.target.value)} />
            </label>
            <label>
              Описание задачи:
              <textarea value={description} onChange={(e) => setDescription(e.target.value)} />
            </label>
            <label>
              Назначить задачу:
              <select value={assignedTo} onChange={(e) => setAssignedTo(e.target.value)}>
                {users.map((user) => (
                  <option key={user.id} value={user.id}>
                    {user.username}
                  </option>
                ))}
              </select>
            </label>
            <button onClick={handleCreateTask}>Создать задачу</button>
          </form>
        </div>
      )}
    </div>
  );
}

export default TaskCreator;
  </script>
</body>
</html>