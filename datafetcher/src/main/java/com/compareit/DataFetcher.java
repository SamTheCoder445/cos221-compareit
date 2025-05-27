package com.compareit;

import com.google.gson.Gson;
import io.github.cdimascio.dotenv.Dotenv;

import java.net.HttpURLConnection;
import java.net.URL;
import java.sql.*;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import java.util.*;

/**
 * This class fetches product data from a public API, processes it, and inserts it into a relational database.
 * It supports product metadata, images, brand/category info, dummy reviews, and price variations per retailer.
 */
public class DataFetcher {

    // Load environment variables from .env file using dotenv library
    static final Dotenv dotenv = Dotenv.load();

    // Database connection details retrieved from .env
    static final String DB_URL = dotenv.get("DB_URL");
    static final String DB_USER = dotenv.get("DB_USER");
    static final String DB_PASSWORD = dotenv.get("DB_PASSWORD");

    // API endpoint to fetch 200 products
    static final String apiUrl = "https://dummyjson.com/products?limit=200";

    // Mapping of product categories to a list of relevant retailers for price generation
    static final Map<String, List<String>> CATEGORY_RETAILER_MAP = new HashMap<>();

    // Static block to initialize category-to-retailer mappings
    static {
        CATEGORY_RETAILER_MAP.put("womens-watches", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("womens-shoes", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("womens-jewellery", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("womens-dresses", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("womens-bags", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));

        CATEGORY_RETAILER_MAP.put("vehicle", Arrays.asList("AutoTrader", "Cars.co.za"));
        CATEGORY_RETAILER_MAP.put("motorcycle", Arrays.asList("AutoTrader", "Cars.co.za"));

        CATEGORY_RETAILER_MAP.put("tops", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("tablets", Arrays.asList("Incredible Connection", "Game", "Amazon", "Makro", "Takealot"));
        CATEGORY_RETAILER_MAP.put("smartphones", Arrays.asList("Incredible Connection", "Game", "Amazon", "Makro", "Takealot"));
        CATEGORY_RETAILER_MAP.put("mobile-accessories", Arrays.asList("Incredible Connection", "Game", "Amazon", "Makro", "Takealot"));

        CATEGORY_RETAILER_MAP.put("sunglasses", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("sports-accessories", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));

        CATEGORY_RETAILER_MAP.put("mens-watches", Arrays.asList("Edgars", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("mens-shoes", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("mens-shirts", Arrays.asList("Ackermans", "Edgars", "Amazon", "Takealot"));

        CATEGORY_RETAILER_MAP.put("laptops", Arrays.asList("Incredible Connection", "Game", "Amazon", "Makro", "Takealot"));

        CATEGORY_RETAILER_MAP.put("kitchen-accessories", Arrays.asList("House and Home", "Bradlows", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("home-decoration", Arrays.asList("House and Home", "Bradlows", "Amazon", "Takealot"));
        CATEGORY_RETAILER_MAP.put("furniture", Arrays.asList("House and Home", "Bradlows", "Amazon", "Takealot"));

        CATEGORY_RETAILER_MAP.put("groceries", Arrays.asList("Pick n Pay", "Checkers", "Spar"));
        CATEGORY_RETAILER_MAP.put("fragrances", Arrays.asList("Amazon", "Edgars", "Takealot"));
        CATEGORY_RETAILER_MAP.put("beauty", Arrays.asList("Amazon", "Edgars", "Takealot"));
        CATEGORY_RETAILER_MAP.put("skin-care", Arrays.asList("Edgars", "Amazon", "Takealot"));
    }

    public static void main(String[] args) {
        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASSWORD)) {
            System.out.println("Fetching from: " + apiUrl);

            // Fetch JSON data from API
            String json = fetchJson(apiUrl);

            // Parse JSON into Java objects using Gson
            Gson gson = new Gson();
            ProductResponse response = gson.fromJson(json, ProductResponse.class);

            // Insert each product and its related data into the database
            for (Product product : response.products) {
                insertProduct(conn, product);
                insertPricesForProduct(conn, product);
            }

            System.out.println("Data insertion completed.");
        } catch (Exception e) {
            e.printStackTrace(); // Print any error that occurs
        }
    }

    /**
     * Fetches JSON data from the provided API URL.
     */
    static String fetchJson(String apiUrl) throws Exception {
        URL url = new URL(apiUrl);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("GET");

        // Read API response line by line
        BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
        String inputLine;
        StringBuilder json = new StringBuilder();
        while ((inputLine = in.readLine()) != null) {
            json.append(inputLine);
        }
        in.close();
        return json.toString();
    }

    /**
     * Inserts a brand into the `brands` table if it doesn't exist, and returns its ID.
     */
    static int insertBrand(Connection conn, String brandName) throws SQLException {
        if (brandName == null || brandName.trim().isEmpty()) {
            brandName = "Unbranded";
        }

        // Check if brand already exists
        String selectSql = "SELECT brand_id FROM brands WHERE name = ?";
        try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
            selectStmt.setString(1, brandName);
            ResultSet rs = selectStmt.executeQuery();
            if (rs.next()) return rs.getInt("brand_id");
        }

        // Insert new brand
        String insertSql = "INSERT INTO brands (name) VALUES (?)";
        try (PreparedStatement insertStmt = conn.prepareStatement(insertSql, Statement.RETURN_GENERATED_KEYS)) {
            insertStmt.setString(1, brandName);
            insertStmt.executeUpdate();
            ResultSet keys = insertStmt.getGeneratedKeys();
            if (keys.next()) return keys.getInt(1);
        }
        throw new SQLException("Failed to insert or retrieve brand.");
    }

    /**
     * Inserts a category into the `categories` table if it doesn't exist, and returns its ID.
     */
    static int insertCategory(Connection conn, String categoryName) throws SQLException {
        String selectSql = "SELECT category_id FROM categories WHERE name = ?";
        try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
            selectStmt.setString(1, categoryName);
            ResultSet rs = selectStmt.executeQuery();
            if (rs.next()) return rs.getInt("category_id");
        }

        // Insert new category
        String insertSql = "INSERT INTO categories (name) VALUES (?)";
        try (PreparedStatement insertStmt = conn.prepareStatement(insertSql, Statement.RETURN_GENERATED_KEYS)) {
            insertStmt.setString(1, categoryName);
            insertStmt.executeUpdate();
            ResultSet keys = insertStmt.getGeneratedKeys();
            if (keys.next()) return keys.getInt(1);
        }
        throw new SQLException("Failed to insert or retrieve category.");
    }

    /**
     * Inserts a product and its related data (images and reviews) into the database.
     */
    static void insertProduct(Connection conn, Product product) throws SQLException {
        int brandId = insertBrand(conn, product.brand);
        int categoryId = insertCategory(conn, product.category);

        // Use REPLACE INTO to update existing products with the same ID
        String sql = "REPLACE INTO products (product_id, title, description, brand_id, category_id, thumbnail, availability_status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, product.id);
            stmt.setString(2, product.title);
            stmt.setString(3, product.description);
            stmt.setInt(4, brandId);
            stmt.setInt(5, categoryId);
            stmt.setString(6, product.thumbnail);
            stmt.setString(7, product.availabilityStatus);
            stmt.executeUpdate();
        }

        if (product.images != null) {
            insertImages(conn, product.id, Arrays.asList(product.images));
        }

        if (product.reviews != null) {
            for (UserReview review : product.reviews) {
                insertReview(conn, product.id, review);
            }
        }
    }

    /**
     * Inserts product image URLs into the `product_images` table.
     */
    static void insertImages(Connection conn, int productId, List<String> imageUrls) throws SQLException {
        String sql = "INSERT INTO product_images (product_id, image_url) VALUES (?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            for (String url : imageUrls) {
                stmt.setInt(1, productId);
                stmt.setString(2, url);
                stmt.addBatch();
            }
            stmt.executeBatch();
        }
    }

    /**
     * Inserts a dummy review into the `dummy_reviews` table.
     */
    static void insertReview(Connection conn, int productId, UserReview review) throws SQLException {
        String sql = "INSERT INTO dummy_reviews (product_id, review_rating, comment, review_date, reviewer_name, reviewer_email) VALUES (?, ?, ?, ?, ?, ?)";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setInt(1, productId);
            stmt.setInt(2, review.rating);
            stmt.setString(3, review.comment);
            String formattedDate = review.date.replace("T", " ").replace("Z", "");
            stmt.setTimestamp(4, Timestamp.valueOf(formattedDate));
            stmt.setString(5, review.reviewerName);
            stmt.setString(6, review.reviewerEmail);
            stmt.executeUpdate();
        }
    }

    /**
     * Inserts price records for the product per applicable retailer with slight random variations.
     */
    static void insertPricesForProduct(Connection conn, Product product) throws SQLException {
        // Determine which retailers sell this category of product
        List<String> eligibleRetailers = CATEGORY_RETAILER_MAP.get(product.category);
        if (eligibleRetailers == null || eligibleRetailers.isEmpty()) return;

        // Cache all retailer IDs from the DB for efficient lookup
        Map<String, Integer> retailerIds = new HashMap<>();
        String sqlRetailer = "SELECT retailer_id, name FROM retailers";
        try (Statement stmt = conn.createStatement(); ResultSet rs = stmt.executeQuery(sqlRetailer)) {
            while (rs.next()) {
                retailerIds.put(rs.getString("name"), rs.getInt("retailer_id"));
            }
        }

        double basePrice = product.price;

        // Insert or update price for each eligible retailer
        String sqlInsert = "INSERT INTO prices (product_id, retailer_id, price) VALUES (?, ?, ?) " +
                           "ON DUPLICATE KEY UPDATE price = VALUES(price)";
        try (PreparedStatement stmt = conn.prepareStatement(sqlInsert)) {
            for (String retailer : eligibleRetailers) {
                Integer retailerId = retailerIds.get(retailer);
                if (retailerId == null) continue;

                // Generate a price between 90% and 110% of the original base price
                double randomFactor = 0.9 + new Random().nextDouble() * 0.2;
                double variedPrice = Math.round(basePrice * randomFactor * 100.0) / 100.0;

                stmt.setInt(1, product.id);
                stmt.setInt(2, retailerId);
                stmt.setDouble(3, variedPrice);
                stmt.addBatch();
            }
            stmt.executeBatch();
        }
    }
}
