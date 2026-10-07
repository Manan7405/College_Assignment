const express = require("express");
const bcrypt = require("bcrypt");
const jwt = require("jsonwebtoken");
const Customer = require("../models/Customer");
const authenticateToken = require("../middleware/authMiddleware");

const router = express.Router();


router.post("/signup", async (req, res) => {
  try {
    const { name, email, password } = req.body;

    if (!name || !email || !password) {
      return res.status(400).json({
        success: false,
        message: "Name, email and password are required"
      });
    }


    const existingCustomer = await Customer.findOne({ email });

    if (existingCustomer) {
      return res.status(409).json({
        success: false,
        message: "Customer already exists"
      });
    }


    const customer = new Customer({
      name,
      email,
      password
    });

    await customer.save();

    res.status(201).json({
      success: true,
      message: "Customer registered successfully",
      customer: {
        id: customer._id,
        name: customer.name,
        email: customer.email
      }
    });
  } catch (error) {
    console.error("Signup error:", error);

    res.status(500).json({
      success: false,
      message: "Server error"
    });
  }
});


router.post("/login", async (req, res) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({
        success: false,
        message: "Email and password are required"
      });
    }

    const customer = await Customer.findOne({ email });

    if (!customer) {
      return res.status(401).json({
        success: false,
        message: "Invalid email or password"
      });
    }

    const isPasswordCorrect =
      await customer.comparePassword(password);

    if (!isPasswordCorrect) {
      return res.status(401).json({
        success: false,
        message: "Invalid email or password"
      });
    }

    const token = jwt.sign(
        {
            id: customer._id,
            email: customer.email,
            role: customer.role
        },
      process.env.JWT_SECRET,
      {
        expiresIn: process.env.JWT_EXPIRES_IN || "1h"
      }
    );

    res.status(200).json({
      success: true,
      message: "Login successful",
      token: token,
      customer: {
        id: customer._id,
        name: customer.name,
        email: customer.email
      }
    });
  } catch (error) {
    console.error("Login error:", error);

    res.status(500).json({
      success: false,
      message: "Server error"
    });
  }
});

router.get("/orders", authenticateToken, async (req, res) => {
  res.status(200).json({
    success: true,
    message: "Protected order history accessed successfully",
    customer: req.customer
  });
});

module.exports = router;