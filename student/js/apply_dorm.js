document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("dorm").addEventListener("change", function () {
        let dormId = this.value;
        let floorElement = document.getElementById("floor");
        if (!floorElement) {
            console.error("Error: Floor selection element not found.");
            return; // Exit the function if floor element is not found
        }
        let floor = floorElement.value; // Add floor selection

        let roomDropdown = document.getElementById("room_id");
        if (roomDropdown) {
            roomDropdown.innerHTML = '<option value="" disabled selected>Loading...</option>';
        } else {
            console.error("Error: Room dropdown element not found.");
            return; // Exit the function if roomDropdown is not found
        }

        fetch(`ajax/fetch_rooms.php?dorm_id=${dormId}&floor=${floor}`)
            .then(response => response.json())
            .then(data => {
                console.log("Fetched Data:", data);

                if (!Array.isArray(data)) {
                    console.error("Error: Expected an array, but got:", data);
                    roomDropdown.innerHTML = '<option value="" disabled selected>Error loading rooms</option>';
                    return;
                }

                roomDropdown.innerHTML = '<option value="" disabled selected>Select a Room</option>';
                data.forEach(room => {
                    roomDropdown.innerHTML += `<option value="${room.room_id}">
                        Room ${room.room_number} (Floor ${room.floor}) - ${room.available_slots} slots left
                    </option>`;
                });
            })
            .catch(error => console.error("Error fetching rooms:", error));
    });
});
