  .container {
            background-color: rgb(252, 251, 248);
            max-width: 1250px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1e3a8a;
            margin: 1.5rem 0;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

         ul li {
            background-color: #fafafa;
            /* border: 1px solid #e0e0e0; */
            /* padding: 12px 15px; */
            border-radius: 8px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: 50px 1fr 60px 60px 100px;
            align-items: center;
            gap: 10px;
            transition: background-color 0.3s, box-shadow 0.3s;
            font-size:17px;
            
        }

        form label {
            /* font-weight: 400; */
            display: block;
            margin-top: 15px;
            font-size:15px;
            margin-bottom: 5px;
            color: #333;
            
        }
        form input[type="text"], form textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            
        }
        form input[type="file"] {
            margin-top: 8px;
        }
        form textarea {
            resize: vertical;
        }
        .custom-select {
            padding: 8px 12px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 150px;
        }
        .existing-image img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        button {
            background-color: rgba(71, 151, 255, 0.81);
            color: white;
            font-size: 15px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 25px;
        }
        div[style^="color:red"] {
            background-color: #ffeaea;
            padding: 10px;
            border-left: 5px solid red;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        div[style^="color:green"] {
            background-color: #e0fbe7;
            padding: 10px;
            border-left: 5px solid green;
            margin-bottom: 20px;
            border-radius: 6px;
        }