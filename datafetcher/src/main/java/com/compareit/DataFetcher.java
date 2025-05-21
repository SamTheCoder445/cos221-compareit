

package com.compareit;

import com.google.gson.Gson;
import io.github.cdimascio.dotenv.Dotenv;

import java.net.HttpURLConnection;
import java.net.URL;
import java.sql.*;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import java.util.*;

public class DataFetcher {

    static final Dotenv dotenv = Dotenv.load();

    static final String DB_URL = dotenv.get("DB_URL");
    static final String DB_USER = dotenv.get("DB_USER");
    static final String DB_PASSWORD = dotenv.get("DB_PASSWORD");

    static final String apiUrl = "https://dummyjson.com/products?limit=200";

    static final Map<String, List<String>> CATEGORY_RETAILER_MAP = new HashMap<>();

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
            String json = fetchJson(apiUrl);
            Gson gson = new Gson();
            ProductResponse response = gson.fromJson(json, ProductResponse.class);

            for (Product product : response.products) {
                insertProduct(conn, product);
                insertPricesForProduct(conn, product);
            }
            System.out.println("Data insertion completed.");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    static String fetchJson(String apiUrl) throws Exception {
        URL url = new URL(apiUrl);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("GET");

        BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
        String inputLine;
        StringBuilder json = new StringBuilder();
        while ((inputLine = in.readLine()) != null) {
            json.append(inputLine);
        }
        in.close();
        return json.toString();
    }

    static int insertBrand(Connection conn, String brandName) throws SQLException {
        if (brandName == null || brandName.trim().isEmpty()) {
            brandName = "Unbranded";
        }
        String selectSql = "SELECT brand_id FROM brands WHERE name = ?";
        try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
            selectStmt.setString(1, brandName);
            ResultSet rs = selectStmt.executeQuery();
            if (rs.next()) return rs.getInt("brand_id");
        }

        String insertSql = "INSERT INTO brands (name) VALUES (?)";
        try (PreparedStatement insertStmt = conn.prepareStatement(insertSql, Statement.RETURN_GENERATED_KEYS)) {
            insertStmt.setString(1, brandName);
            insertStmt.executeUpdate();
            ResultSet keys = insertStmt.getGeneratedKeys();
            if (keys.next()) return keys.getInt(1);
        }
        throw new SQLException("Failed to insert or retrieve brand.");
    }

    static int insertCategory(Connection conn, String categoryName) throws SQLException {
        String selectSql = "SELECT category_id FROM categories WHERE name = ?";
        try (PreparedStatement selectStmt = conn.prepareStatement(selectSql)) {
            selectStmt.setString(1, categoryName);
            ResultSet rs = selectStmt.executeQuery();
            if (rs.next()) return rs.getInt("category_id");
        }

        String insertSql = "INSERT INTO categories (name) VALUES (?)";
        try (PreparedStatement insertStmt = conn.prepareStatement(insertSql, Statement.RETURN_GENERATED_KEYS)) {
            insertStmt.setString(1, categoryName);
            insertStmt.executeUpdate();
            ResultSet keys = insertStmt.getGeneratedKeys();
            if (keys.next()) return keys.getInt(1);
        }
        throw new SQLException("Failed to insert or retrieve category.");
    }

    static void insertProduct(Connection conn, Product product) throws SQLException {
        int brandId = insertBrand(conn, product.brand);
        int categoryId = insertCategory(conn, product.category);

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


static void insertPricesForProduct(Connection conn, Product product) throws SQLException {
    List<String> eligibleRetailers = CATEGORY_RETAILER_MAP.get(product.category);
    if (eligibleRetailers == null || eligibleRetailers.isEmpty()) return;

    // Loads all retailer IDs once
    Map<String, Integer> retailerIds = new HashMap<>();
    String sqlRetailer = "SELECT retailer_id, name FROM retailers";
    try (Statement stmt = conn.createStatement(); ResultSet rs = stmt.executeQuery(sqlRetailer)) {
        while (rs.next()) {
            retailerIds.put(rs.getString("name"), rs.getInt("retailer_id"));
        }
    }

    double basePrice = product.price;

    String sqlInsert = "INSERT INTO prices (product_id, retailer_id, price) VALUES (?, ?, ?) " +
                       "ON DUPLICATE KEY UPDATE price = VALUES(price)";
    try (PreparedStatement stmt = conn.prepareStatement(sqlInsert)) {
        for (String retailer : eligibleRetailers) {
            Integer retailerId = retailerIds.get(retailer);
            if (retailerId == null) continue;

            // Generate a price between 90% and 110% of the base price
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
