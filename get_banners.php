<?php
        $servername = "localhost"; 
        $username = "root"; 
        $password = ""; 
        $dbname = "cinemajs";

        $conn = new mysqli($servername, $username, $password, $dbname); 
            if ($conn->connect_error) { 
                die("Connection error: " . $conn->connect_error);
            } 
            
        $sql = "SELECT image_path FROM banners"; 
        $result = $conn->query($sql); 
            
            // Wyświetlanie obrazow obrazy
            if ($result->num_rows > 0) {
                 while($row = $result->fetch_assoc()) { 
                    echo '<img src="' . $row["image_path"] . '" width="300" height="300">'; 
                }
             } 
            else { 
                echo "0 wyników"; 
            } 
        
        $conn->close();

        ?>