
### 有些程式會啟動 server, 要在那裡看網頁
```
開啟瀏灠器, 嘗試以下的網址
    http://localhost/
    http://localhost:8080/
```

### 從 windows 跳出的視窗, 顯示的中文是亂碼
```
https://superuser.com/questions/675369/what-encoding-to-get-%C3%85-%C3%84-%C3%96-to-work

在視窗的標題上按滑鼠右鍵
選擇 "內容"

在跳出的 "Command Prompt" 視窗選 "Font"
    將字型選為 "Lucida Console"
    按下 "OK"
    然後關窗視窗

下次再開視窗的時候
    中文就不會是亂碼了
```


### 如果是在 Linux 上面要怎麼執行
```
依照你選定 bin/______.bat 檔案裡面指定的路徑
輸入類似以下的指令

    php -S localhost:8080 src/_your_project_/public/index.php
```