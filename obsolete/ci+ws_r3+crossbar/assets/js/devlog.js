function consLog(log, data0 = null, data1 = null, data2 = null) {
    switch (log) {
    case "chat_delmsg_attempt":
        console.info(`Attempting to delete message with ID: ${data0}`);
        console.groupCollapsed("Delete Message (attempting)");
        console.debug({data0});
        console.debug({data1});
        console.trace();
        console.groupEnd();
        break;
    case "chat_delmsg_error":
        console.error("Error deleting message:", data2);
        console.groupCollapsed("Delete Message (error)");
        console.debug({data0});
        console.debug({data1});
        console.debug({data2});
        console.trace();
        console.groupEnd();
        break;
    case "chat_delmsg_fail":
        console.error("Failed to delete message:", data2);
        console.groupCollapsed("Delete Message (failed)");
        console.debug({data0});
        console.debug({data1});
        console.debug({data2});
        console.trace();
        console.groupEnd();
        break;
    case "chat_delmsg_success":
        console.info("Message deleted successfully with ID:", data0);
        console.groupCollapsed("Delete Message (success)");
        console.debug({data0});
        console.debug({data1});
        console.trace();
        console.groupEnd();
        break;
    case "chat_rendmsg_attempt":
        console.info("Rendering message:", data0);
        console.groupCollapsed("Render Message (attempting)");
        console.debug({data0});
        console.debug({data1});
        console.trace();
        console.groupEnd();
        break;
    case "chat_rendmsg_sidmiss":
        console.error("Message Sender ID is missing:", data0);
        console.groupCollapsed("Render Message (Sender ID is missing)");
        console.debug({data0});
        console.debug({data1});
        console.debug({data2});
        console.trace();
        console.groupEnd();
        break;
    }
}
