require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const authRoutes = require('./routes/authRoutes');
const postRoutes = require('./routes/postRoutes');
const uiRoutes = require('./routes/uiRoutes');

const app = express();

app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      scriptSrc: ["'self'", "'unsafe-inline'"], // Allows our EJS scripts
      styleSrc: ["'self'", "'unsafe-inline'"],  // Allows our EJS styles
      imgSrc: ["'self'", "data:", "blob:"]      // Allows uploaded images to display
    }
  }
}));
app.use(express.json());
app.use('/uploads', express.static('uploads'));
app.set('view engine', 'ejs');

const authLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, 
  max: 10, 
  message: { error: 'Too many requests from this IP, please try again after 15 minutes.' }
});

mongoose.connect(process.env.MONGO_URI)
  .then(() => console.log('Connected to MongoDB successfully!'))
  .catch((err) => console.error('MongoDB connection error:', err));

app.use('/api/auth', authLimiter, authRoutes);
app.use('/api/posts', postRoutes);
app.use('/', uiRoutes);

app.get('/', (req, res) => {
  res.send('Blog Platform API is running securely!');
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});