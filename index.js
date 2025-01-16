import axios from 'axios';
import process from 'process';

process.on('uncaughtException', (err) => {
    console.error('Необработанная ошибка:', err);
});

process.on('SIGTERM', () => {
    console.log('Получен сигнал завершения, выключаюсь...');
    process.exit(0);
});

// Увеличиваем интервал до 10 секунд
const UPDATE_INTERVAL = 10000; 

// Добавляем флаги для отслеживания выполнения
let isUpdatingEnergy = false;
let isUpdatingCoins = false;

const updateEnergy = async () => {
    if (isUpdatingEnergy) return; // Пропускаем если предыдущий запрос еще выполняется
    
    try {
        isUpdatingEnergy = true;
        const response = await axios.get('http://brawl-coin.com/update/energy');
        console.log('update energy');
    } catch (error) {
        console.error('Ошибка при обновлении энергии:', error.message);
    } finally {
        isUpdatingEnergy = false;
    }
};

const updateCoins = async () => {
    if (isUpdatingCoins) return; // Пропускаем если предыдущий запрос еще выполняется
    
    try {
        isUpdatingCoins = true;
        const response = await axios.get(`http://brawl-coin.com/update/coins/1234567890abcdefghijklmnopqrstuvwxyz`);
        console.log('update coins');
    } catch (error) {
        console.error('Ошибка при обновлении монет:', error.message);
    } finally {
        isUpdatingCoins = false;
    }
};

// Запускаем обновления с интервалом в 10 секунд
setInterval(updateEnergy, UPDATE_INTERVAL);
setInterval(updateCoins, UPDATE_INTERVAL);

console.log('Сервис обновления запущен с интервалом', UPDATE_INTERVAL/1000, 'секунд...');