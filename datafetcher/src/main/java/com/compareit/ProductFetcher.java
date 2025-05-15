
package com.compareit;

import com.google.gson.Gson;
import io.github.cdimascio.dotenv.Dotenv;

import java.net.HttpURLConnection;
import java.net.URL;
import java.sql.*;
import java.io.InputStreamReader;
import java.io.BufferedReader;
import com.compareit.Product;
import com.compareit.ProductResponse;

public class ProductFetcher {

    static final Dotenv dotenv = Dotenv.load();

    static final String DB_URL = dotenv.get("DB_URL");
    static final String DB_USER = dotenv.get("DB_USER");
    static final String DB_PASSWORD = dotenv.get("DB_PASSWORD");

    static final String apiUrl = "https://dummyjson.com/products?limit=200";

    public static void main(String[] args) {
        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASSWORD)) {
            System.out.println("Fetching from: " + apiUrl);
            String json = fetchJson(apiUrl);
            Gson gson = new Gson();
            ProductResponse response = gson.fromJson(json, ProductResponse.class);

            for (Product product : response.products) {
                insertProduct(conn, product);
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

    // static void insertProduct(Connection conn, Product product) throws SQLException {
    //     String sql = "REPLACE INTO products (product_id, title, description, brand, category, thumbnail, average_rating, availability_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    //     try (PreparedStatement stmt = conn.prepareStatement(sql)) {
    //         stmt.setInt(1, product.id);
    //         stmt.setString(2, product.title);
    //         stmt.setString(3, product.description);
    //         stmt.setString(4, product.brand);
    //         stmt.setString(5, product.category);
    //         stmt.setString(6, product.thumbnail);
    //         stmt.setFloat(7, product.rating);
    //         stmt.setString(8, product.availabilityStatus);
    //         stmt.executeUpdate();
    //     }
    // }



    static void insertProduct(Connection conn, Product product) throws SQLException {
    String sql = "REPLACE INTO products (product_id, title, description, brand, category, thumbnail, average_rating, availability_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    try (PreparedStatement stmt = conn.prepareStatement(sql)) {
        stmt.setInt(1, product.id);
        stmt.setString(2, product.title);
        stmt.setString(3, product.description);
        stmt.setString(4, product.brand);
        stmt.setString(5, product.category);
        stmt.setString(6, product.thumbnail);
        stmt.setFloat(7, product.rating);
        stmt.setString(8, product.availabilityStatus);
        stmt.executeUpdate();
    }

    if (product.reviews != null) {
        for (UserReview review : product.reviews) {
            insertReview(conn, product.id, review);
        }
    }
}

static void insertReview(Connection conn, int productId, UserReview review) throws SQLException {
    String sql = "INSERT INTO user_reviews (product_id, review_rating, comment, date, reviewer_name, reviewer_email) VALUES (?, ?, ?, ?, ?, ?)";
    try (PreparedStatement stmt = conn.prepareStatement(sql)) {
        stmt.setInt(1, productId);
        stmt.setInt(2, review.rating);
        stmt.setString(3, review.comment);
        stmt.setTimestamp(4, Timestamp.valueOf(review.date.replace("T", " ").replace("Z", ""))); // Format ISO date
        stmt.setString(5, review.reviewerName);
        stmt.setString(6, review.reviewerEmail);
        stmt.executeUpdate();
    }
}

}
