import Masonry from "masonry-layout";
import imagesLoaded from "imagesloaded"; 

document.addEventListener("DOMContentLoaded", function () {
    let grid = document.querySelector(".container");

    let msnry = new Masonry(grid, {
        itemSelector: ".pin",
        columnWidth: 200,
        gutter: 10,
        fitWidth: true
    });

    imagesLoaded(grid, function () {
        msnry.layout();
    });
});
