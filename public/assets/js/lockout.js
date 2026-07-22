'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const alertBox = document.getElementById('lockout-alert');

    if (!alertBox) {
        return;
    }

    const countdown = document.getElementById('lockout-countdown');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const loginButton = document.getElementById('login-button');

    const initialSeconds = Number.parseInt(
        alertBox.dataset.remainingSeconds ?? '0',
        10
    );

    if (!Number.isFinite(initialSeconds) || initialSeconds <= 0) {
        return;
    }

    const unlockTime = Date.now() + (initialSeconds * 1000);

    const formatTime = (seconds) => {
        const minutes = Math.floor(seconds / 60);
        const remaining = seconds % 60;

        return `${String(minutes).padStart(2, '0')}:${String(remaining).padStart(2, '0')}`;
    };

    const enableForm = () => {
        emailInput.disabled = false;
        passwordInput.disabled = false;
        loginButton.disabled = false;

        alertBox.textContent = 'You may try signing in again now.';
        passwordInput.focus();
    };

    let timer = null;

    const updateCountdown = () => {
        const secondsLeft = Math.max(
            0,
            Math.ceil((unlockTime - Date.now()) / 1000)
        );

        countdown.textContent = formatTime(secondsLeft);

        if (secondsLeft === 0) {
            if (timer !== null) {
                window.clearInterval(timer);
            }

            enableForm();
        }
    };

    updateCountdown();
    timer = window.setInterval(updateCountdown, 1000);
});