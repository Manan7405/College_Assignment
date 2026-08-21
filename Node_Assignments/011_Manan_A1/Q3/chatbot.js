// Domain-specific knowledge base (College Inquiry Support)
const knowledgeBase = {
    admission: "Admissions for MSC-ICT open in June. You need a relevant Bachelor's degree with at least 50%.",
    fees: "The academic fee is ₹35,000 per semester plus applicable examination fees.",
    courses: "We offer MCA, MSC-ICT, M.Tech, and various specialized PG diplomas.",
    timings: "College operating hours are 9:00 AM to 5:00 PM, Monday through Saturday.",
    contact: "You can reach the admission desk at support@college.edu or call +91-9876543210.",
    location: "Our campus is located at University Campus, City Center."
};

/**
 * Processes user input and returns a domain-specific answer.
 * @param {string} input - User query from terminal
 * @returns {string} - Chatbot response
 */
function getBotResponse(input) {
    if (!input || typeof input !== 'string') {
        return "Please type a valid question.";
    }

    const cleanInput = input.trim().toLowerCase();

    // Check for greetings
    if (['hi', 'hello', 'hey', 'namaste'].some(greet => cleanInput.includes(greet))) {
        return "Hello! I am your College Assistant bot. You can ask me about: admission, fees, courses, timings, or contact.";
    }

    // Match keywords against domain knowledge
    for (const [key, answer] of Object.entries(knowledgeBase)) {
        if (cleanInput.includes(key)) {
            return answer;
        }
    }

    // Default fallback if query is outside the domain
    return "I'm sorry, I don't have information on that. Try asking about: admission, fees, courses, timings, or contact.";
}

// Export the chatbot function as a reusable Node.js module
module.exports = {
    getBotResponse
};