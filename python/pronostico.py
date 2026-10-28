import json
import sys

import pandas as pd
from sklearn.linear_model import LinearRegression


def pronosticar(ventas, dias=7):
    ventas = [max(0, float(v)) for v in ventas]
    dias = max(1, int(dias))

    if not ventas:
        return 0.0

    df = pd.DataFrame({
        "dia": range(len(ventas)),
        "ventas": ventas
    })

    if len(df) < 2:
        return float(df["ventas"].mean() * dias)

    modelo = LinearRegression()
    modelo.fit(df[["dia"]], df["ventas"])

    futuros = pd.DataFrame({
        "dia": range(len(df), len(df) + dias)
    })

    predicciones = modelo.predict(futuros)

    return max(0.0, float(predicciones.sum()))


if __name__ == "__main__":
    entrada = sys.stdin.read().strip()

    if entrada:
        try:
            datos = json.loads(entrada)
            ventas = datos.get("ventas", [])
            dias = datos.get("dias", 7)

            resultado = pronosticar(ventas, dias)

            print(json.dumps({
                "demanda_predicha": round(resultado, 2),
                "dias": int(dias),
                "observaciones": len(ventas)
            }))
        except Exception as error:
            print(json.dumps({
                "error": str(error)
            }))
            sys.exit(1)
    else:
        ventas = [15, 18, 17, 20, 22, 19, 24]
        resultado = pronosticar(ventas, 7)

        print(
            "Demanda estimada próximos 7 días:",
            round(resultado, 2)
        )