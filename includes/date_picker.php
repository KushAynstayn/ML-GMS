<div class="flex shrink-0 items-center gap-3">
    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500">
        <span class="text-xs font-bold uppercase">From</span>
        <input type="date" id="startDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer text-sm">
    </div>
    
    <div id="toDateContainer" class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-gray-500">
        <span class="text-xs font-bold uppercase">To</span>
        <input type="date" id="endDate" onchange="filterTable()" class="focus:outline-none bg-transparent cursor-pointer text-sm">
    </div>
</div>

<script>
// Logic shared across all pages using the date picker
function checkDateMatch(dateText, startDateValue, endDateValue) {
    if (startDateValue && endDateValue) {
        const [d, m, y] = dateText.split('/');
        const rowDate = new Date(y, m - 1, d).setHours(0,0,0,0);
        const start = new Date(startDateValue).setHours(0,0,0,0);
        const end = new Date(endDateValue).setHours(0,0,0,0);
        
        return rowDate >= start && rowDate <= end;
    }
    return true; // Default to true if dates aren't fully selected
}
</script>