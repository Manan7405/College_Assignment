function showBanner() {
    console.log("==================================================");
    console.log("         COLLEGE HELPDESK CHATBOT (CLI)          ");
    console.log("==================================================");
    console.log("Type your question below (or type 'exit' to quit).\n");
}

function showExitMessage() {
    console.log("\n==================================================");
    console.log(" Thank you for using College Bot. Have a nice day! ");
    console.log("==================================================");
}

const readline = require('readline');
const { getBotResponse } = require('./chatbot'); 

// 1. Create readline interface for reading from terminal (stdin) and writing to terminal (stdout)
const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout,
    prompt: '\nYou: '
});

// 2. Display introductory banner
showBanner();
rl.prompt();

// 3. Listen for user input line by line
rl.on('line', (line) => {
    const userInput = line.trim();

    // 4. Handle exit command
    if (userInput.toLowerCase() === 'exit' || userInput.toLowerCase() === 'quit') {
        rl.close();
        return;
    }

    // 5. Pass input to chatbot module and print answer
    const botReply = getBotResponse(userInput);
    console.log(`Bot: ${botReply}`);

    // 6. Prompt user for next input
    rl.prompt();
});

// 7. Handle interface close (exit)
rl.on('close', () => {
    showExitMessage();
    process.exit(0);
});