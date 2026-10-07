const express = require("express");
const Order = require("../models/Order");
const authenticateToken = require("../middleware/authMiddleware");

const router = express.Router();

router.delete("/:id", authenticateToken, async (req, res) => {
  try {
    const order = await Order.findById(req.params.id);

    if (!order) {
      return res.status(404).json({
        success: false,
        message: "Order not found"
      });
    }

    const isAdmin = req.customer.role === "admin";

    const isOrderOwner =
      order.customer.toString() === req.customer.id.toString();

    if (!isAdmin && !isOrderOwner) {
      return res.status(403).json({
        success: false,
        message: "Forbidden. You can only cancel your own order."
      });
    }

    if (!isAdmin && order.status === "shipped") {
      return res.status(403).json({
        success: false,
        message: "Order cannot be cancelled after it has shipped."
      });
    }


    order.status = "cancelled";

    await order.save();

    res.status(200).json({
      success: true,
      message: "Order cancelled successfully",
      order: {
        id: order._id,
        customer: order.customer,
        status: order.status
      }
    });
  } catch (error) {
    console.error("Cancel order error:", error);

    res.status(500).json({
      success: false,
      message: "Server error"
    });
  }
});

module.exports = router;