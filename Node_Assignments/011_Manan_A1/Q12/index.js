function registerUser() {
    return new Promise((resolve) => {
        setTimeout(() => {
            console.log('1. User registered');
            resolve('manan@example.com');
        }, 1000);
    });
}

function sendWelcomeEmail(email) {
    return new Promise((resolve) => {
        setTimeout(() => {
            console.log('2. Welcome email sent to', email);
            resolve(email);
        }, 1000);
    });
}

function createUserProfile(email) {
    return new Promise((resolve) => {
        setTimeout(() => {
            console.log('3. Profile created for', email);
            resolve('All dependent tasks completed');
        }, 1000);
    });
}

registerUser()
    .then((email) => sendWelcomeEmail(email))
    .then((email) => createUserProfile(email))
    .then((message) => {
        console.log(message);
    })
    .catch((error) => {
        console.log('Error:', error.message);
    });
