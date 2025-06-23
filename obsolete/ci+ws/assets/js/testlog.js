function consLog(log, data0 = null, data1 = null, data2 = null) {
    switch (log) {
    case "chat_delmsg_attempt":
        console.info("Attempting to delete message.");
        break;
    case "chat_delmsg_error":
        console.error("Error deleting message.");
        break;
    case "chat_delmsg_fail":
        console.error("Failed to delete message.");
        break;
    case "chat_delmsg_success":
        console.info("Message deleted successfully.");
        break;
    case "chat_rendmsg_attempt":
        console.info("Rendering message.");
        break;
    case "chat_rendmsg_sidmiss":
        console.error("Message Sender ID is missing.");
        break;
    }
}
