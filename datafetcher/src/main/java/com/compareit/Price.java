package com.compareit;

public class Price {
    public int productId;
    public int retailerId;
    public double price;

    public Price(int productId, int retailerId, double price) {
        this.productId = productId;
        this.retailerId = retailerId;
        this.price = price;
    }
}
