document.addEventListener("DOMContentLoaded", function () {
    initResponsiveTable()
});

function initResponsiveTable() {
    const tableContainers = document.querySelectorAll(".c-table-block__container");

    // Create mobile cards for a specific table
    function createMobileCards(tableContainer) {
        const rowHeadings = tableContainer.querySelectorAll(
            ".c-table-block__row-headings .c-table-block__heading"
        );
        const rows = tableContainer.querySelectorAll(".c-table-block__row");

        // Check if headings and rows exist
        if (rowHeadings.length === 0 || rows.length === 0) return null;

        const mobileContainer = document.createElement("div");
        mobileContainer.classList.add("mobile-table");

        const headers = Array.from(rowHeadings);
        rows.forEach((row) => {
            const card = createMobileCard(headers, row);
            if (card) {
                mobileContainer.appendChild(card);
            }
        });

        // Hide the original table
        tableContainer.style.display = "none";

        // Insert the mobile cards container after the original table
        tableContainer.parentNode.insertBefore(mobileContainer, tableContainer.nextSibling);

        return mobileContainer;
    }

    // Create a single mobile card for a table row, ignoring empty columns
    function createMobileCard(headers, row) {
        const card = document.createElement("div");
        card.classList.add("mobile-card");

        const cells = row.querySelectorAll(".ctable-block__text, p");
        let hasContent = false;

        cells.forEach((cell, index) => {
            const cellContent = cell.textContent.trim();
            if (cellContent) {
                hasContent = true;
                const field = document.createElement("div");
                field.classList.add("mobile-card-field");

                const label = document.createElement("strong");
                label.textContent = headers[index].textContent;
                field.appendChild(label);

                const value = document.createElement("span");
                value.textContent = cellContent;
                field.appendChild(value);

                card.appendChild(field);
            }
        });

        // Return the card only if it has at least one field with content
        return hasContent ? card : null;
    }

    // Transform all tables into mobile cards
    function transformToMobile() {
        tableContainers.forEach((tableContainer) => {
            // Prevent duplicate transformation
            if (!tableContainer._mobileContainer) {
                tableContainer._mobileContainer = createMobileCards(tableContainer);
            }
        });
    }

    // Restore all tables to their original desktop view
    function restoreToDesktop() {
        tableContainers.forEach((tableContainer) => {
            if (tableContainer._mobileContainer) {
                tableContainer._mobileContainer.remove();
                tableContainer._mobileContainer = null;
            }
            tableContainer.style.display = "block";
        });
    }

    // Handle resize and toggle transformation/restoration
    function handleResize() {
        if (window.innerWidth <= 990) {
            transformToMobile();
        } else {
            restoreToDesktop();
        }
    }

    // Initial check and resize listener
    handleResize();
    window.addEventListener("resize", handleResize);
}
