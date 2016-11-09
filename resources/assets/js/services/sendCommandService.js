import ajaxUtils from './ajaxUtilsService';

export default {
  send(command, data, callback) {
    if (data && typeof data !== "object") {
      data = { path: data };
    }

    const uri = "sendCommand?cmd=" + command;

    // data.cmd = command;
    ajaxUtils.post(uri, data, (res) => {
      if (typeof callback === "function") {
        callback(res);
      }
    });
  },
}
