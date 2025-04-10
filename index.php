<?php
function getRandomText($level) {
    $words = file('words.txt', FILE_IGNORE_NEW_LINES);
    shuffle($words);

    $count = [
        'easy' => 50,
        'medium' => 100,
        'hard' => 150,
        'hardcore' => 300
    ];

    $selected = array_slice($words, 0, $count[$level]);
    return implode(' ', $selected);
}

if (isset($_GET['level'])) {
    echo getRandomText($_GET['level']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Rakstīšanas Spēle</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-3xl bg-gray-800 p-6 rounded-2xl shadow-2xl space-y-6">
        <h1 class="text-3xl font-bold text-center text-indigo-400">⏱️ Rakstīšanas Spēle</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input id="nickname" type="text" placeholder="Nickname"
                   class="col-span-1 md:col-span-1 p-2 rounded-xl bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            
            <select id="difficulty" class="p-2 rounded-xl bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="easy">Easy (50)</option>
                <option value="medium">Medium (100)</option>
                <option value="hard">Hard (150)</option>
                <option value="hardcore">Hardcore (300)</option>
            </select>

            <button id="start-button" class="bg-indigo-600 hover:bg-indigo-700 transition p-2 rounded-xl text-white font-semibold">
                🚀 Sākt Spēli
            </button>
        </div>

        <div class="text-right text-sm text-indigo-400">
            WPM: <span id="wpm" class="font-bold text-white">0</span>
        </div>

        <div id="text-display" class="p-4 bg-gray-700 rounded-xl min-h-[120px] space-y-2 leading-relaxed font-mono text-base"></div>

        <textarea id="input-area" rows="5"
                  class="w-full bg-gray-700 rounded-xl p-4 text-white font-mono resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="Raksti šeit..." disabled></textarea>
    </div>

    <script>
        let startTime, interval, words;
        const textDisplay = document.getElementById('text-display');
        const inputArea = document.getElementById('input-area');
        const wpmDisplay = document.getElementById('wpm');
        const startBtn = document.getElementById('start-button');

        startBtn.addEventListener('click', async () => {
            const level = document.getElementById('difficulty').value;
            const nickname = document.getElementById('nickname').value.trim();
            if (!nickname) return alert("Lūdzu ievadi segvārdu!");

            const res = await fetch(`index.php?level=${level}`);
            const text = await res.text();
            words = text.split(' ');

            inputArea.disabled = false;
            inputArea.value = '';
            inputArea.focus();

            textDisplay.innerHTML = words.map(w => `<span>${w}</span>`).join(' ');
            startTime = new Date().getTime();

            if (interval) clearInterval(interval);
            interval = setInterval(updateWPM, 1000);
        });

        inputArea.addEventListener('input', () => {
            const inputWords = inputArea.value.trim().split(' ');
            const spans = textDisplay.querySelectorAll('span');

            inputWords.forEach((word, i) => {
                if (!spans[i]) return;
                if (word === words[i]) {
                    spans[i].className = 'text-green-400 font-semibold';
                } else {
                    spans[i].className = 'text-red-500 font-semibold';
                }
            });

            if (inputWords.length === words.length && inputWords.every((w, i) => w === words[i])) {
                finishGame();
            }
        });

        function updateWPM() {
            const now = new Date().getTime();
            const minutes = (now - startTime) / 60000;
            const typedWords = inputArea.value.trim().split(' ').length;
            const wpm = Math.round(typedWords / minutes);
            wpmDisplay.textContent = wpm;
        }

        function finishGame() {
            clearInterval(interval);
            inputArea.disabled = true;

            const timeTaken = (new Date().getTime() - startTime) / 1000;
            const nickname = document.getElementById('nickname').value;
            const level = document.getElementById('difficulty').value;

            fetch('save_score.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname, level, time: timeTaken })
            }).then(() => alert("Spēle pabeigta! Rezultāts saglabāts."));
        }
    </script>
</body>
</html>
