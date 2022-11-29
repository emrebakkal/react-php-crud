import react, { useEffect, useState } from "react";
import axios from "axios";
import { Button, Stack, Input, Center, UnorderedList, ListItem } from "@chakra-ui/react";
import toast, { Toaster } from 'react-hot-toast';

export default function App() {
    const [data, setData] = useState([]);
    const [todo, setTodo] = useState('');

    const getTodos = async () => {
        const response = await axios.get('http://localhost/cookieders/api/todos');
        setData(response.data);
    }

    useEffect(() => {
        getTodos();
    }, []);

    var formData = new FormData();
    const config = { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } };

    const addTodo = (e) => {
        e.preventDefault();
        formData.append('todo', todo);
        formData.append('is_completed', 0);
        
        axios.post('http://localhost/cookieders/api/create', formData, config)
            .then(res => {
                console.log(res);
                if (!res.data.error_message) {
                    getTodos();
                    toast.success(res.data.message);
                    setTodo('');
                } else {
                    toast.error(res.data.error_message);
                }
                
            })
            .catch(err => {
                console.log(err);
            });
            
        
    }

  return (
      <Stack>
            <h1 style={({textAlign: "center", fontSize: "30px", fontWeight: "bold", marginTop: "10px"})}>React & PHP Todo App</h1>
            <Center>
                <Input
                    placeholder="Enter a todo"
                    variant='outline'
                    focusBorderColor="black"
                    w="30%"
                    textAlign="center"
                    mt="10"
                    onChange={(e) => setTodo(e.target.value)}
                    value={todo}
                />
            </Center>
          <Button w={100} style={({margin: '0 auto', marginTop: "10px"})} onClick={addTodo}>Add Todo</Button>
            <UnorderedList style={({textAlign: "center", marginTop: "30px"})}>
                {data.length >= 1 ? data.map((todo) => (
                    <ListItem key={todo.todo_id} style={({ cursor: "pointer" })}
                              onClick={(e) => { axios.patch(`http://localhost/cookieders/api/complete/${todo.todo_id}`).then(() => e.target.classList.add('completed')) }}
                              onDoubleClick={(e) => { axios.patch(`http://localhost/cookieders/api/uncomplete/${todo.todo_id}`).then(() => e.target.classList.remove('completed')) }}
                    >
                        {todo.todo}
                        <Button style={({marginLeft: "10px", marginBottom: "10px"})} size="sm" onClick={(e) => {
                            axios.delete(`http://localhost/cookieders/api/delete/${todo.todo_id}`).then((res) => {
                            if (!res.data.error_message)  
                            {     
                            toast.success(res.data.message);
                            getTodos(); 
                        } else {
                            toast.error(res.data.error_message);
                        }

                            })
                        }}>
                            Delete
                        </Button>
                        <Button style={({marginLeft: "10px", marginBottom: "10px"})} size="sm"
                            onClick={() => {
                                var newTodo = prompt("Enter new todo");
                                axios.patch(`http://localhost/cookieders/api/change/${todo.todo_id}/${newTodo}`).then((res) => {
                                    getTodos();
                                    if (!res.data.error_message) {
                                        toast.success(res.data.message);
                                    } else {
                                        if (newTodo == null) {
                                        toast.error(res.data.error_message);
                                        } else {
                                            toast.error(res.data.error_message);
                                        }
                                    }
                                })
                                
                            }}
                        >
                            Edit
                        </Button>
                    </ListItem>
                )) : <p>There is no todo's but you can add.</p>}
            </UnorderedList>
            <Toaster 
                position="bottom-center"
                reverseOrder={false}
            />
      </Stack>
  )
}