const canvas = document.getElementById('mazeCanvas');
const ctx = canvas.getContext('2d');

// Dynamically adjust canvas size
canvas.width = window.innerWidth - 20;  // Subtract 20px for margins
canvas.height = window.innerHeight - 20; // Subtract 20px for margins

// Adjust cellSize to make walls thinner
const cellSize = Math.floor(Math.min(canvas.width, canvas.height) / 60); // Try dividing by 60 for thinner cells

let maze, player, steps, mazeSize, level, pathToExit;
const finishEmoji = "🏁";
const powers = {
    foresight: { active: false, cooldown: 0 },
    randomization: { active: false },
    intangibility: { active: false, steps: 0 }
};

const DIRECTIONS = [
    { x: 0, y: -1 },
    { x: 0, y: 1 },
    { x: -1, y: 0 },
    { x: 1, y: 0 }
];

const emojis = ['😀', '😁', '😂', '🤣', '😃', '😄', '😅', '😆', '😉', '😊', 
    '😎', '😍', '😘', '😜', '😝', '😋', '😇', '🤩', '🥳', '😺'];

function initCharacterSelection() {
    const emojiList = document.getElementById('emojiList');
    emojis.forEach(emoji => {
        const button = document.createElement('button');
        button.textContent = emoji;
        button.onclick = () => selectEmoji(emoji);
        emojiList.appendChild(button);
    });
    document.getElementById('startGameButton').disabled = true;
}

function selectEmoji(emoji) {
    player = { x: 1, y: 1, emoji };
    document.getElementById('startGameButton').disabled = false;
    document.getElementById('startGameButton').onclick = initGame;
}

function initGame() {
    document.getElementById('characterSelection').style.display = 'none';
    level = prompt("Choose difficulty level: normal, hard, extreme");
    mazeSize = level === 'hard' ? 50 : level === 'extreme' ? 60 : 40;
    steps = 0;
    maze = generateMaze(mazeSize);
    pathToExit = findPathToExit(maze);
    drawMaze();
}

function generateMaze(size) {
    const maze = Array.from({ length: size }, () => Array(size).fill(1));
    const stack = [{ x: 1, y: 1 }];
    maze[1][1] = 0;
    while (stack.length) {
        const current = stack.pop();
        const neighbors = DIRECTIONS.map(d => ({ x: current.x + d.x * 2, y: current.y + d.y * 2 }))
            .filter(n => n.x > 0 && n.y > 0 && n.x < size - 1 && n.y < size - 1 && maze[n.y][n.x] === 1);
        if (neighbors.length) {
            stack.push(current);
            const next = neighbors[Math.floor(Math.random() * neighbors.length)];
            maze[next.y][next.x] = 0;
            maze[(current.y + next.y) / 2][(current.x + next.x) / 2] = 0;
            stack.push(next);
        }
    }
    maze[size - 3][size - 2] = 0;
    maze[size - 2][size - 3] = 0;
    maze[size - 2][size - 2] = 0;
    return maze;
}

function findPathToExit(maze) {
    const queue = [{ x: 1, y: 1, path: [] }];
    const visited = new Set();
    while (queue.length) {
        const { x, y, path } = queue.shift();
        if (x === maze.length - 2 && y === maze.length - 2) return path;
        DIRECTIONS.forEach(({ x: dx, y: dy }) => {
            const nx = x + dx, ny = y + dy;
            if (maze[ny] && maze[ny][nx] === 0 && !visited.has(`${nx},${ny}`)) {
                visited.add(`${nx},${ny}`);
                queue.push({ x: nx, y: ny, path: [...path, { x: nx, y: ny }] });
            }
        });
    }
    return [];
}

function drawMaze() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.translate(cellSize, cellSize);
    maze.forEach((row, i) => row.forEach((cell, j) => {
        ctx.fillStyle = cell ? 'black' : 'white';
        ctx.fillRect(j * cellSize, i * cellSize, cellSize, cellSize);
    }));
    if (powers.foresight.active) {
        ctx.fillStyle = 'rgba(0, 255, 0, 0.5)';
        pathToExit.forEach(({ x, y }) => ctx.fillRect(x * cellSize, y * cellSize, cellSize, cellSize));
    }
    ctx.font = `${cellSize}px Arial`;
    ctx.fillText(player.emoji, player.x * cellSize + 5, player.y * cellSize + 15);
    ctx.fillText(finishEmoji, (mazeSize - 2) * cellSize + 5, (mazeSize - 2) * cellSize + 15);
    ctx.resetTransform();
}

function movePlayer(dx, dy) {
    if (powers.intangibility.active && powers.intangibility.steps > 0) {
        player.x += dx;
        player.y += dy;
        powers.intangibility.steps--;
    } else if (maze[player.y + dy][player.x + dx] === 0) {
        player.x += dx;
        player.y += dy;
    }
    steps++;
    document.getElementById('steps').textContent = steps; // Update step counter
    drawMaze();
    if (player.x >= mazeSize - 3 && player.y >= mazeSize - 3) alert("You Win!");
}

document.addEventListener('keydown', event => {
    const moves = { 'ArrowUp': [0, -1], 'ArrowDown': [0, 1], 'ArrowLeft': [-1, 0], 'ArrowRight': [1, 0] };
    if (moves[event.key]) movePlayer(...moves[event.key]);
});

document.getElementById('foresight').addEventListener('click', () => {
    if (!powers.foresight.active) {
        powers.foresight.active = true;
        drawMaze();
        setTimeout(() => { powers.foresight.active = false; drawMaze(); }, 3000);
    }
});

document.getElementById('randomize').addEventListener('click', () => { maze = generateMaze(mazeSize); pathToExit = findPathToExit(maze); drawMaze(); });

document.getElementById('intangibility').addEventListener('click', () => {
    if (!powers.intangibility.active) {
        powers.intangibility.active = true;
        powers.intangibility.steps = 10;
        setTimeout(() => powers.intangibility.active = false, 10000);
    }
});

initCharacterSelection();
