import Vue from "vue";
import Vuex from "vuex";
import moodleAjax from "core/ajax";
import Notification from "core/notification";
import mainStore from "./main_store";

Vue.use(Vuex);

export const store = new Vuex.Store({
  state: mainStore.state,
  getters: mainStore.getters,
  mutations: mainStore.mutations,
  actions: mainStore.actions,
});

/**
 * Wrapper for ajax call to Moodle with course module context automatically set.
 */
export async function ajax(method, args) {
  const request = {
    methodname: method,
    args: Object.assign(
      {
        coursemoduleid: store.state.courseModuleID,
      },
      args
    ),
  };

  try {
    return await moodleAjax.call([request])[0];
  } catch (e) {
    Notification.exception(e);
    throw e;
  }
}
