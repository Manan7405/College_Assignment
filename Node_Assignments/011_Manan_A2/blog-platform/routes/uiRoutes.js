const express = require('express');
const router = express.Router();
const Post = require('../models/Post');

router.get('/register', (req, res) => {
  res.render('register');
});

router.get('/login', (req, res) => {
  res.render('login');
});

router.get('/dashboard', (req, res) => {
  res.render('dashboard');
});

router.get('/posts', async (req, res) => {
  try {
    const posts = await Post.find({ published: true }).populate('author', 'name');
    res.render('posts', { posts });
  } catch (error) {
    res.status(500).send('Error loading posts');
  }
});

module.exports = router;