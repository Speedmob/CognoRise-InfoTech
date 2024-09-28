<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ping Pong Game</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #1E1E1E, #333);
        }
        canvas {
            border: 3px solid white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <canvas id="gameCanvas"></canvas>
    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');

        const WIDTH = 800, HEIGHT = 600;
        canvas.width = WIDTH;
        canvas.height = HEIGHT;

        const WHITE = 'rgb(255, 255, 255)';
        const ORANGE = 'rgb(255, 165, 0)';
        const BLUE = 'rgb(0, 0, 255)';

        const PADDLE_WIDTH = 10, PADDLE_HEIGHT = 100;
        const PADDLE_SPEED = 5;

        const BALL_SIZE = 16;
        let BALL_SPEED_X = 5, BALL_SPEED_Y = 5;

        let playerPaddle = { x: 10, y: HEIGHT / 2 - PADDLE_HEIGHT / 2, width: PADDLE_WIDTH, height: PADDLE_HEIGHT };
        let aiPaddle = { x: WIDTH - 20, y: HEIGHT / 2 - PADDLE_HEIGHT / 2, width: PADDLE_WIDTH, height: PADDLE_HEIGHT };
        let ball = { x: WIDTH / 2 - BALL_SIZE / 2, y: HEIGHT / 2 - BALL_SIZE / 2, size: BALL_SIZE };

        let playerScore = 0;
        let aiScore = 0;

        const keys = {};

        document.addEventListener('keydown', function (e) {
            keys[e.key] = true;
        });

        document.addEventListener('keyup', function (e) {
            keys[e.key] = false;
        });

        function resetBall() {
            ball.x = WIDTH / 2 - BALL_SIZE / 2;
            ball.y = HEIGHT / 2 - BALL_SIZE / 2;
            BALL_SPEED_Y *= Math.random() < 0.5 ? -0.5 : 0.5;
            BALL_SPEED_X = -5;
        }

        function changeBallAngle(paddle) {
            const offset = (ball.y + BALL_SIZE / 2 - (paddle.y + PADDLE_HEIGHT / 2)) / (PADDLE_HEIGHT / 2);
            BALL_SPEED_Y = 5 * offset;
        }

        function predict() {
            const ttt_X = (WIDTH - ball.x) / Math.abs(BALL_SPEED_X);
            const ttt_Y = (ball.y + BALL_SIZE / 2 < HEIGHT / 2) ? (ball.y + BALL_SIZE / 2) / Math.abs(BALL_SPEED_Y) : (HEIGHT - ball.y) / Math.abs(BALL_SPEED_Y);
            return (ttt_X - ttt_Y > 0) ? (BALL_SPEED_Y > 0 ? HEIGHT - (ttt_X - ttt_Y) * Math.abs(BALL_SPEED_Y) : (ttt_X - ttt_Y) * Math.abs(BALL_SPEED_Y)) : -99999;
        }

        function gameLoop() {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
            ctx.fillRect(0, 0, WIDTH, HEIGHT);

            if (keys['ArrowUp'] && playerPaddle.y > 0) {
                playerPaddle.y -= PADDLE_SPEED;
            }
            if (keys['ArrowDown'] && playerPaddle.y < HEIGHT - PADDLE_HEIGHT) {
                playerPaddle.y += PADDLE_SPEED;
            }

            let predictionY = predict();
            if (BALL_SPEED_X < 0) {
                if (aiPaddle.y + PADDLE_HEIGHT / 2 > HEIGHT / 2) {
                    aiPaddle.y -= PADDLE_SPEED;
                } else if (aiPaddle.y + PADDLE_HEIGHT / 2 < HEIGHT / 2) {
                    aiPaddle.y += PADDLE_SPEED;
                }
            } else {
                if ((WIDTH - ball.x) > WIDTH / 5 && predictionY !== -99999) {
                    if (aiPaddle.y + PADDLE_HEIGHT / 2 > predictionY && aiPaddle.y > 0) {
                        aiPaddle.y -= PADDLE_SPEED;
                    } else if (aiPaddle.y + PADDLE_HEIGHT / 2 < predictionY && aiPaddle.y + PADDLE_HEIGHT < HEIGHT) {
                        aiPaddle.y += PADDLE_SPEED;
                    }
                } else if (predictionY === -99999) {
                    if (aiPaddle.y + PADDLE_HEIGHT < ball.y + BALL_SIZE / 2 && aiPaddle.y + PADDLE_HEIGHT < HEIGHT) {
                        aiPaddle.y += PADDLE_SPEED;
                    } else if (aiPaddle.y > ball.y - 40 && aiPaddle.y > 0) {
                        aiPaddle.y -= PADDLE_SPEED;
                    }
                }
            }

            ball.x += BALL_SPEED_X;
            ball.y += BALL_SPEED_Y;

            if (ball.y < 0 || ball.y + BALL_SIZE > HEIGHT) {
                BALL_SPEED_Y *= -1;
            }

            if (ball.x < playerPaddle.x + PADDLE_WIDTH && ball.y + BALL_SIZE > playerPaddle.y && ball.y < playerPaddle.y + PADDLE_HEIGHT) {
                BALL_SPEED_X *= -1 * 1.05;
                changeBallAngle(playerPaddle);
            }

            if (ball.x + BALL_SIZE > aiPaddle.x && ball.y + BALL_SIZE > aiPaddle.y && ball.y < aiPaddle.y + PADDLE_HEIGHT) {
                BALL_SPEED_X *= -1 * 1.05;
                changeBallAngle(aiPaddle);
            }

            if (ball.x <= 0) {
                playerScore += 1;
                resetBall();
            }
            if (ball.x + BALL_SIZE >= WIDTH) {
                aiScore += 1;
                resetBall();
            }

            ctx.fillStyle = ORANGE;
            ctx.roundRect(playerPaddle.x, playerPaddle.y, PADDLE_WIDTH, PADDLE_HEIGHT, 10).fill();
            ctx.fillStyle = BLUE;
            ctx.roundRect(aiPaddle.x, aiPaddle.y, PADDLE_WIDTH, PADDLE_HEIGHT, 10).fill();
            ctx.fillStyle = WHITE;
            ctx.beginPath();
            ctx.arc(ball.x + BALL_SIZE / 2, ball.y + BALL_SIZE / 2, BALL_SIZE / 2, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = WHITE;
            ctx.font = '30px Arial';
            ctx.fillText(`Score: ${playerScore}`, WIDTH / 4, 40);
            ctx.fillText(`Score: ${aiScore}`, (WIDTH * 3) / 4 - 80, 40);

            requestAnimationFrame(gameLoop);
        }

        CanvasRenderingContext2D.prototype.roundRect = function (x, y, width, height, radius) {
            this.beginPath();
            this.moveTo(x + radius, y);
            this.lineTo(x + width - radius, y);
            this.quadraticCurveTo(x + width, y, x + width, y + radius);
            this.lineTo(x + width, y + height - radius);
            this.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            this.lineTo(x + radius, y + height);
            this.quadraticCurveTo(x, y + height, x, y + height - radius);
            this.lineTo(x, y + radius);
            this.quadraticCurveTo(x, y, x + radius, y);
            this.closePath();
            return this;
        };

        resetBall();
        gameLoop();
    </script>
</body>
</html>