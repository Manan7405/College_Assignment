const authorizeAdmin = (req, res, next) => {
  if (!req.customer || req.customer.role !== "admin") {
    return res.status(403).json({
      success: false,
      message: "Forbidden. Admin access required."
    });
  }

  next();
};

module.exports = authorizeAdmin;