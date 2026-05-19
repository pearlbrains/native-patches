import Foundation

enum TestFunctions {

    class Execute: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let param1 = parameters["param1"] as? String ?? ""

            return BridgeResponse.success([
                "status": "executed",
                "param1": param1
            ])
        }
    }

    class GetData: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            return BridgeResponse.success([
                "data": "native_data",
                "timestamp": Date().timeIntervalSince1970
            ])
        }
    }
}