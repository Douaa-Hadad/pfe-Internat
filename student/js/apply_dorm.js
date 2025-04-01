document.addEventListener("DOMContentLoaded", function () {
    let dormSelect = document.getElementById("dorm");
    let roomDropdown = document.getElementById("room_id");

    if (!dormSelect || !roomDropdown) {
        console.error("Dorm or Room dropdown is missing.");
        return;
    }

    dormSelect.addEventListener("change", function () {
        let dormId = dormSelect.value;
        roomDropdown.innerHTML = '<option value="" disabled selected>Loading...</option>';

        fetch(`ajax/fetch_rooms.php?dorm_id=${dormId}`)
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
                    if (room && room.room_id && room.room_number && room.available_slots !== undefined) {
                        roomDropdown.innerHTML += `<option value="${room.room_id}">
                            Room ${room.room_number} - ${room.available_slots} slots left
                        </option>`;
                    } else {
                        console.error("Invalid room data:", room);
                    }
                });
            })
            .catch(error => console.error("Error fetching rooms:", error));
    });
});
