const express = require("express");
const authenticateToken = require("../middleware/authMiddleware");
const authorizeAdmin = require("../middleware/adminMiddleware");

const router = express.Router();

router.get(
  "/inventory",
  authenticateToken,
  authorizeAdmin,
  async (req, res) => {
    res.status(200).json({
      success: true,
      message: "Admin inventory dashboard accessed successfully",
      data: {
        books: [
          {
            title: "Node.js Guide",
            stock: 25,
            sales: 10
          },
          {
            title: "MongoDB Basics",
            stock: 15,
            sales: 7
          },
          {
            title: "Express.js Handbook",
            stock: 20,
            sales: 12
          }
        ]
      }
    });
  }
);

module.exports = router;