package com.test.plugin

import androidx.fragment.app.FragmentActivity
import android.content.Context
import com.nativephp.mobile.bridge.BridgeFunction
import com.nativephp.mobile.bridge.BridgeResponse

object TestFunctions {

    class Execute(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val param1 = parameters["param1"] as? String ?: ""

            // Test implementation
            return BridgeResponse.success(mapOf(
                "status" to "executed",
                "param1" to param1
            ))
        }
    }

    class GetData(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            return BridgeResponse.success(mapOf(
                "data" to "native_data",
                "timestamp" to System.currentTimeMillis()
            ))
        }
    }
}