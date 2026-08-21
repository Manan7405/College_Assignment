// Function executed when the HTML button is clicked
function testStaticJS() {
    const statusBox = document.getElementById('status-log');
    
    // Demonstrate that the browser successfully fetched and ran the static JS file
    const currentTime = new Date().toLocaleTimeString();
    statusBox.innerText = `Static JS executed successfully at ${currentTime}!`;
}