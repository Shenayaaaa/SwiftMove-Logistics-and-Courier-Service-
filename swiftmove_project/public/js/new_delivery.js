document.addEventListener("DOMContentLoaded", function () {
    const calculateBtn = document.getElementById("calculatePrice");
    const pickupSelect = document.getElementById("pickup_location");
    const dropoffSelect = document.getElementById("dropoff_location");
    const deliveryTypeSelect = document.getElementById("delivery_type");
    const packageSizeSelect = document.getElementById("package_size");
    const weightInput = document.getElementById("weight");
    const estimatedPriceInput = document.getElementById("estimated_price");

    calculateBtn.addEventListener("click", function () {
        // Get selected values
        const pickup = pickupSelect.value;
        const dropoff = dropoffSelect.value;
        const deliveryTypeOption = deliveryTypeSelect.options[deliveryTypeSelect.selectedIndex];
        const packageSizeOption = packageSizeSelect.options[packageSizeSelect.selectedIndex];

        if (!pickup || !dropoff || !deliveryTypeOption.value || !packageSizeOption.value) {
            alert("Please select valid pickup, drop-off, delivery type, and package size.");
            return;
        }

        // Lookup the route to get distance
        let distance = null;
        for (let i = 0; i < routesData.length; i++) {
            const route = routesData[i];
            if (route.pickup_location === pickup && route.dropoff_location === dropoff) {
                distance = parseFloat(route.distance);
                break;
            }
        }

        if (distance === null) {
            alert("No route found for the selected pickup and drop-off locations.");
            return;
        }

        // Get additional cost for delivery type and fixed price for package size
        const deliveryAdditionalCost = parseFloat(deliveryTypeOption.getAttribute("data-additional-cost")) || 0;
        const packageFixedPrice = parseFloat(packageSizeOption.getAttribute("data-fixed-price")) || 0;

        // Calculate total price: base rate per km * distance + additional cost + fixed package size price
        const totalPrice = (distance * baseRate) + deliveryAdditionalCost + packageFixedPrice;

        // Set the estimated price field (round to 2 decimals)
        estimatedPriceInput.value = totalPrice.toFixed(2);
    });
});
