import "./bootstrap";

/* This JavaScript code snippet is setting up a real-time notification system using Laravel Echo and
Pusher. Here's a breakdown of what each part of the code is doing: */
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: process.env.VITE_PUSHER_APP_KEY,
    cluster: process.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

// Play sound when a new notification arrives
function playNotificationSound() {
    let audio = new Audio("/sounds/noti.wav"); // Adjust URL if necessary
    audio.play().catch((error) => console.log("Error playing sound:", error));
}

// Listen for notifications
window.Echo.private("agent." + userId).notification((notification) => {
    playNotificationSound(); // 🔔 Play notification sound

    $("#notificationCount").text(parseInt($("#notificationCount").text()) + 1);
    $(".dropdown-menu").prepend(
        `<li>
                <a href="#" class="dropdown-item">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h3 class="dropdown-item-title">${notification.player_name}</h3>
                            <p class="fs-7">${notification.message}</p>
                        </div>
                    </div>
                </a>
            </li>`
    );
});

// import Echo from "laravel-echo";
// import Pusher from "pusher-js";

// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: "pusher",
//     key: process.env.VITE_PUSHER_APP_KEY,
//     cluster: process.env.VITE_PUSHER_APP_CLUSTER,
//     forceTLS: true,
// });

// window.Echo.private("agent." + userId).notification((notification) => {
//     $("#notificationCount").text(parseInt($("#notificationCount").text()) + 1);
//     $(".dropdown-menu").prepend(
//         `<li>
//                 <a href="#" class="dropdown-item">
//                     <div class="d-flex">
//                         <div class="flex-grow-1">
//                             <h3 class="dropdown-item-title">${notification.player_name}</h3>
//                             <p class="fs-7">${notification.message}</p>
//                         </div>
//                     </div>
//                 </a>
//             </li>`
//     );
// });

// import Echo from "laravel-echo";
// import Pusher from "pusher-js";

// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: "pusher",
//     key: process.env.PUSHER_APP_KEY,
//     cluster: process.env.PUSHER_APP_CLUSTER,
//     forceTLS: true,
// });

// window.Echo.private("App.Models.User." + userId).notification(
//     (notification) => {
//         $("#notificationCount").text(
//             parseInt($("#notificationCount").text()) + 1
//         );
//         $(".dropdown-menu").prepend(
//             `<li>
//                 <a href="#" class="dropdown-item">
//                     <div class="d-flex">
//                         <div class="flex-grow-1">
//                             <h3 class="dropdown-item-title">${notification.player_name}</h3>
//                             <p class="fs-7">${notification.message}</p>
//                         </div>
//                     </div>
//                 </a>
//             </li>`
//         );
//     }
// );
